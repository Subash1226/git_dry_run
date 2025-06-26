<?php
namespace Lof\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

abstract class Order implements ObserverInterface
{
    protected $_cacheTypeList;
    protected $coreRegistry;
    protected $orderFactory;
    protected $creditmemo;
    protected $quoteCollectionFactory;
    protected $quoteRepository;
    protected $objectManager;
    protected $messageManager;
    protected $storeManager;
    protected $checkoutSession;
    protected $rewardsData;
    protected $rewardsBalanceOrder;
    protected $rewardsBalanceEarn;
    protected $rewardsBalanceSpend;
    protected $rewardsBalance;
    protected $rewardsPurchase;
    protected $rewardsCustomer;
    protected $rewardsMail;
    protected $rewardsLogger;
    protected $rewardsConfig;
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\PurchaseFactory
     */
    protected $purchaseFactory;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Balance\Order $rewardsBalanceOrder,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Lof\RewardPoints\Model\PurchaseFactory $purchaseFactory,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
    ) {
        $this->coreRegistry                 = $coreRegistry;
        $this->orderFactory                 = $orderFactory;
        $this->creditmemo                   = $creditmemo;
        $this->quoteCollectionFactory       = $quoteCollectionFactory;
        $this->messageManager               = $messageManager;
        $this->objectManager                = $objectManager;
        $this->quoteRepository              = $quoteRepository;
        $this->storeManager                 = $storeManager;
        $this->checkoutSession              = $checkoutSession;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsBalanceOrder          = $rewardsBalanceOrder;
        $this->rewardsBalanceEarn           = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend          = $rewardsBalanceSpend;
        $this->rewardsBalance               = $rewardsBalance;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsCustomer              = $rewardsCustomer;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsConfig                = $rewardsConfig;
        $this->purchaseFactory              = $purchaseFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->_cacheTypeList               = $cacheTypeList;
    }

    public function getConfig()
    {
        return $this->rewardsConfig;
    }
}
