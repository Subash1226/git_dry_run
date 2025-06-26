<?php
namespace Apptha\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Apptha\Marketplace\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;

class Product implements ObserverInterface {
    protected $marketplaceData;
    protected $systemHelper;
    protected $messagemanager;
    protected $_request;
    protected $_redirectFactory;

    /**
     * Constructor
     *
     * @param Data $marketplaceData
     * @param \Apptha\Marketplace\Helper\System $systemHelper
     * @param ManagerInterface $messagemanager
     * @param HttpRequest $request
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Data $marketplaceData,
        \Apptha\Marketplace\Helper\System $systemHelper,
        ManagerInterface $messagemanager,
        HttpRequest $request,
        RedirectFactory $redirectFactory
    ) {
        $this->marketplaceData = $marketplaceData;
        $this->systemHelper = $systemHelper;
        $this->messagemanager = $messagemanager;
        $this->_request = $request;
        $this->_redirectFactory = $redirectFactory;
    }

    /**
     * Function to check seller product or not
     *
     * @see \Magento\Framework\Event\ObserverInterface::execute()
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $product = $observer->getProduct();
        $productSellerId = $product->getSellerId();
        
        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->get(CustomerSession::class);
        $customerId = '';

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
        }

        if ($productSellerId == $customerId) {
            $this->messagemanager->addError("Seller can't add their own product");
            $cartUrl = $objectManager->get(CartHelper::class)->getCartUrl();
            
            // Create a redirect result object
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->_redirectFactory->create();
            $resultRedirect->setUrl($cartUrl);
            
            // Return the result object
            return $resultRedirect;
        }
    }
}
