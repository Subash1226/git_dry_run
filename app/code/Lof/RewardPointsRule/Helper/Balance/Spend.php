<?php
namespace Lof\RewardPointsRule\Helper\Balance;

use Lof\RewardPoints\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Lof\RewardPointsRule\Model\Earing;
use Lof\RewardPointsRule\Model\Spending as SpendingRule;

class Spend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Lof\RewardPointsRule\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $spendingRuleCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Lof\RewardPointsRule\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\Tax\Helper\Data $taxHelper,
        \Lof\RewardPointsRule\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($context);
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->messageManager = $messageManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->taxHelper = $taxHelper;
        $this->customerSession = $customerSession;
        $this->rewardsLogger = $rewardsLogger;
    }

    /**
     * Get store
     *
     * @param string $storeId
     * @return \Magento\Store\Model\Store
     */
    public function getStore($storeId = '')
    {
        $this->store = $this->storeManager->getStore($storeId);
        return $this->store;
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer;
    }

    /**
     * Get rules collection
     *
     * @param string $type
     * @return \Lof\RewardPointsRule\Model\ResourceModel\Spending\Collection
     */
    public function getRules($type = '')
    {
        $collection = $this->spendingRuleCollectionFactory->create();
        $store = $this->getStore();
        $storeId = $store->getId();
        $customerGroupId = $this->getCustomer()->getGroupId();
        $collection->addStatusFilter()
            ->addDateFilter();
        if ($type) {
            $collection->addFieldToFilter('type', $type);
        }
        $collection->addStoreFilter($storeId)
            ->addCustomerGroupFilter($customerGroupId);
        $collection->getSelect()
            ->order('main_table.sort_order ASC')
            ->order('main_table.rule_id DESC');
        return $collection;
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $store = $this->getStore();
        $collection = $this->productCollectionFactory->create();
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($store->getStoreId());
        return $collection;
    }

    /**
     * Check if product prices include tax
     *
     * @return bool
     */
    public function isIncludeTax()
    {
        $store = $this->getStore();
        $priceIncludesTax = $this->taxHelper->priceIncludesTax($store);
        return $priceIncludesTax;
    }

    /**
     * Get product spending rules
     *
     * @param Product $product
     * @return array
     */
    public function getProductSpendingRules(Product $product)
    {
        $collection = $this->getProductCollection();
        $rules = $this->getRules(SpendingRule::PRODUCT_RULE);
        $products = $points = [];
        $product->setEarningPoints(0);
        $products[$product->getId()] = $product;

        $spendingRules = [];

        try {
            foreach ($rules as $_rule) {
                $newCollection = $collection;
                $newCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                $conditions = $_rule->getConditions();
                $conditions->collectValidatedAttributes($newCollection);
                $this->sqlBuilder->attachConditionToCollection($newCollection, $conditions);
                $ids = $newCollection->getAllIds();
                foreach ($ids as $k => $v) {
                    if(isset($products[$v])){
                        $spendingRules[] = $_rule;
                        break;
                    }
                }
                if( $_rule->getIsStopProcessing() ) break;
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $spendingRules;
    }
}
