<?php
namespace Apptha\Marketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class CheckLogin implements ObserverInterface
{
    protected $messageManager;
    protected $responseFactory;
    protected $url;
    protected $redirect;
    protected $catalogSession;
    protected $session; // Declare the $_session property
    protected $productCollectionFactory;

    public function __construct(
        Session $session,
        ResponseFactory $responseFactory,
        ManagerInterface $messageManager,
        UrlInterface $url,
        RedirectInterface $redirect,
        CatalogSession $catalogSession,
        CollectionFactory $productCollectionFactory
    ) {
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->redirect = $redirect;
        $this->catalogSession = $catalogSession;
        $this->productCollectionFactory = $productCollectionFactory;
    }
    
    public function execute(Observer $observer)
    {
        $items = $this->session->getQuote()->getAllItems();
        foreach ($items as $item) {
            $productId = $item->getProductId();
            
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*');
            $productCollection->addFieldToFilter('entity_id', $productId);
            
            foreach ($productCollection as $product) {
                $productSellerId = $product->getSellerId();
                /** @var CustomerSession $customerSession */
                $customerSession = \Magento\Framework\App\ObjectManager::getInstance()->get(CustomerSession::class);
                $customerId = '';

                if ($customerSession->isLoggedIn()) {
                    $customerId = $customerSession->getId();
                    
                    if ($productSellerId == $customerId) {
                        $this->catalogSession->setMyValue__("Seller can't add their own product");
                        $this->responseFactory->create()->setRedirect('/');
                        exit;
                    }
                }
            }
        }
    }
}
?>