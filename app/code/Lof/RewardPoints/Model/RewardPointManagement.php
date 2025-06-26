<?php
namespace Lof\RewardPoints\Model;


use Lof\RewardPoints\Api\RewardPointManagementInterface;
use Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory as SpendingCollection;
use Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Lof\RewardPoints\Api\Data\RewardPointsSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class RewardPointManagement implements RewardPointManagementInterface
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;
    /**
     * @var SpendingCollection
     */
    protected $rewardpointsCollection;
    /**
     * @var SpendingCollection
     */
    protected $spendingCollectionFactory;
    /**
     * @var RewardPointsSearchResultsInterfaceFactory
     */
    protected $searchResults;
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    private $rewardsData;
    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    private $rewardsPurchase;
    private $spending;
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    private $rewardsCustomer;
    /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $rewardsCheckout;
    public function __construct( \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
                                 RewardPointsSearchResultsInterfaceFactory $searchResults,
                                 CollectionFactory $transactionCollectionFactory,
                                 SpendingCollection $spendingCollectionFactory,
                                 \Lof\RewardPoints\Helper\Data $rewardsData,
                                 \Lof\RewardPoints\Helper\Checkout $rewardsCheckout,
                                 \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
                                 Spending $spending,
                                 \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
                                 UserContextInterface $userContext,
                                 CartRepositoryInterface $quoteRepository,                         
                                 CustomerCollection $rewardpointsCollection) {
        $this->purchaseCollectionFactory    = $purchaseCollectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardpointsCollection       = $rewardpointsCollection;
        $this->spendingCollectionFactory    = $spendingCollectionFactory;
        $this->searchResults                = $searchResults;
        $this->rewardsData                   = $rewardsData;
        $this->rewardsPurchase               = $rewardsPurchase;
        $this->spending                      = $spending;
        $this->rewardsCustomer               = $rewardsCustomer;
        $this->rewardsCheckout               = $rewardsCheckout;
        $this->userContext                  = $userContext;
        $this->quoteRepository              = $quoteRepository;

    }

    /**
     * Get the active quote ID for the current customer
     * 
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getActiveQuoteId()
    {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            throw new NoSuchEntityException(__('Customer is not logged in'));
        }

        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
            return $quote->getId();
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('No active quote found for the customer'));
        }
    }

    /**
     * GET spending available points by customer
     * @param int|null $cartId
     * @return mixed
     */
    public function getTotalSpentPoint($cartId = null)
    {
        if (!$cartId) {
            $cartId = $this->getActiveQuoteId();
        }
        
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $cartId);
        foreach ($collection->getItems() as $spendpoints) {
            $result[]['spend_points'] = $spendpoints->getSpendPoints();
        }

        return $result;
    }
    
    public function getTransaction($customer_id)
    {
        $result = [];
        $collection = $this -> transactionCollectionFactory->create();
        $collection->addFieldToFilter('customer_id',$customer_id);
        foreach ($collection->getItems() as $transaction)
        {
            $result[]['data'] = $transaction->getData();
        }
        return $result;

    }

    /**
     * @inheritDoc
     */
    public function getTotalCustomerPoints($customerId)
    {
        $result = [];
        $connection = $this->rewardpointsCollection->create();
        $connection->addFieldToFilter('customer_id', $customerId);
        foreach ($connection->getItems() as $points) {
            $result[]['available_points'] = $points->getData()['available_points'];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getOrderEarnPoints($order_id)
    {
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $order_id);
        foreach ($collection->getItems() as $earnpoints) {

            $result[]['earn_points'] = $earnpoints->getEarnPoints();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getOrderEarnSpentPoints($order_id)
    {
        return $this->getOrderEarnPoints($order_id);
    }

    /**
     * GET List spend rule in cart
     * @param int $cartId
     * @return mixed
     */
    public function getListSpendingRule($cartId)
    {
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $cartId);
        foreach ($collection->getItems() as $rule) {

            $result[]['list_rule_by_cart'] = $rule->getParams();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $Collection = $this->spendingCollectionFactory->create()
            ->setCurPage(1);
        $searchResults = $this->searchResults->create();
        $searchResults->setItems($Collection->getItems());
        $searchResults->setTotalCount($Collection->getSize());
        return $searchResults;
    }
    
   /**
     * Apply or cancel points in cart based on action
     * 
     * @param int|null $cartId
     * @param string $action (apply|cancel)
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyPoint($action, $cartId = null)
    {
        if (!$cartId) {
            $cartId = $this->getActiveQuoteId();
        }
        $result = [];
        $quote = $this->rewardsData->getQuote($cartId);
        
        // Get customer ID from quote
        $customerId = $quote->getCustomerId();
        if (!$customerId) {
            throw new NoSuchEntityException(__('Cannot identify the customer for this cart'));
        }
        
        // Get customer using the customer ID
        $customer = $this->rewardsCustomer->getCustomer($customerId);
        
        // Get the latest spending rule
        $spendingCollection = $this->spendingCollectionFactory->create();
        $spendingCollection->setOrder('rule_id', 'DESC');
        $spendingCollection->setPageSize(1);
        
        $latestRule = $spendingCollection->getFirstItem();
        if (!$latestRule->getId()) {
            throw new NoSuchEntityException(__('No spending rules defined in the system'));
        }
        
        $spendingRuleId = $latestRule->getId();
        $spendingRate = (int)$latestRule->getData()['spend_points'];
        $monetaryStep = (int)$latestRule->getData()['monetary_step'];
        $ruleMin = (int)$latestRule->getData()['spend_min_points'];
        $ruleMax = (int)$latestRule->getData()['spend_max_points'];
        
        $spendPoint = 0;
        $discount = 0;
        
        if ($action == 'apply') {
            // Get customer's available points
            $availablePoints = (int)$customer->getAvailablePoints();
            if ($availablePoints <= 0) {
                throw new NoSuchEntityException(__('Customer has no available points'));
            }
            
            // Calculate 20% of available points
            $spendPoint = (int)($availablePoints * 0.2);
            
            // Validate if the spend points meet the rule criteria
            if ($spendPoint < $ruleMin) {
                $spendPoint = $ruleMin;
            }
            
            if ($ruleMax > 0 && $spendPoint > $ruleMax) {
                $spendPoint = $ruleMax;
            }
            
            // Ensure we don't exceed the maximum eligible points for this cart
            $maxPointsForCart = ($quote->getSubtotal() / $monetaryStep) * $spendingRate;
            if ($spendPoint > $maxPointsForCart) {
                $spendPoint = (int)$maxPointsForCart;
            }
            
            // Don't allow spending less than the minimum rule requirement
            if ($spendPoint < $spendingRate) {
                throw new NoSuchEntityException(__('Minimum required points for this rule is %1', $spendingRate));
            }
            
            // Calculate discount amount
            $discount = ($spendPoint * $monetaryStep) / $spendingRate;
        } else if ($action == 'cancel') {
            // For cancel, we simply set spendPoint and discount to 0
            $spendPoint = 0;
            $discount = 0;
        } else {
            throw new NoSuchEntityException(__('Invalid action. Use "apply" or "cancel".'));
        }
        
        // Apply the points (or cancel by applying 0)
        $array = [
            'isAjax' => 'true',
            'spendpoints' => $spendPoint,
            'discount' => $discount,
            'rule' => $spendingRuleId,
            'stepdiscount' => $spendingRate,
            'quote' => $cartId,
            'rulemin' => $ruleMin,
            'rulemax' => $ruleMax,
        ];
        
        $this->rewardsCheckout->applyPoints($array);
        $customer->refreshPoints()->save();
        
        // Get purchase for earn points information
        $purchase = $this->rewardsPurchase->getPurchase($quote);
        
        // Prepare response message
        $message = ($action == 'apply') 
            ? __('Successfully applied %1 reward points', $spendPoint)
            : __('Reward points have been canceled');
        
        $availablePoints = (int)$customer->getAvailablePoints();
        
        $result[] = [
            'success' => true,
            'message' => $message,
            'spend_points' => $spendPoint,
            'grand_total' => $quote->getGrandTotal(),
            'subtotal' => $quote->getSubtotal(),
            'discount' => $discount,
            'earn_points' => (int)$purchase->getEarnPoints(),
            'rule_used' => $spendingRuleId,
            'available_points' => $availablePoints,
        ];
        
        return $result;
    }
}