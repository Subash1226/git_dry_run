<?php
namespace Apptha\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Model\Customer;
use Apptha\Marketplace\Helper\Data as MarketplaceHelper;
use Apptha\Marketplace\Helper\Email as MarketplaceEmailHelper;

class Cataloginventorysave implements ObserverInterface {
    protected $action;
    protected $_productRepository;
    protected $scopeConfig;

    public function __construct(
        Action $action,
        ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->action = $action;
        $this->_productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $sellerNotification = $this->scopeConfig->getValue('marketplace/seller/seller_lowstock');
        $minimumQuantity = $this->scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if ($sellerNotification == "1") {
            $orderId = $observer->getEvent()->getOrder()->getId();
            $order = $this->loadOrder($orderId);
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $items) {
                $productId = $items->getProductId();
                $product = $this->loadProduct($productId);

                if ($product->getTypeId() == 'configurable') {
                    $options = $this->getOptions($product);
                    foreach ($options as $sku => $d) {
                        $pr = $this->_productRepository->get($sku);
                        $product = $this->_productRepository->getById($pr->getId());
                        $inventory = $product->getQuantityAndStockStatus();
                        $remainingQuantity = $inventory['qty'];
                        $productSellerId = $product->getSellerId();
                        $this->sendEmail($productSellerId, $remainingQuantity, $minimumQuantity, $product);
                    }
                } else {
                    $productStockObj = $this->getStockItem($productId);
                    $remainingQuantity = $productStockObj->getQty();
                    $productSellerId = $productStockObj->getSellerId();
                    $this->sendEmail($productSellerId, $remainingQuantity, $minimumQuantity, $product);
                }
            }
        }
    }

    protected function loadOrder($orderId) {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(Order::class)->load($orderId);
    }

    protected function loadProduct($productId) {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepository::class)->getById($productId);
    }

    protected function getStockItem($productId) {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(StockRegistryInterface::class)->getStockItem($productId);
    }

    public function getOptions($product) {
        $configData = $product->getTypeInstance()->getConfigurableOptions($product);
        $options = [];
        foreach ($configData as $attr) {
            foreach ($attr as $p) {
                $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
            }
        }
        return $options;
    }

    public function sendEmail($productSellerId, $remainingQuantity, $minimumQuantity, $product) {
        $seller = \Magento\Framework\App\ObjectManager::getInstance()->create(Customer::class)->load($productSellerId);
        $adminHelper = \Magento\Framework\App\ObjectManager::getInstance()->create(MarketplaceHelper::class);
        $adminName = $adminHelper->getAdminName();
        $adminEmail = $adminHelper->getAdminEmail();

        $senderInfo = [
            'name' => $adminName,
            'email' => $adminEmail
        ];

        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail()
        ];

        $emailTempVariables = [
            'receivername' => $seller->getName(),
            'productname' => $product->getName(),
            'sku' => $product->getSku(),
            'qty' => $remainingQuantity,
            'minqty' => $minimumQuantity
        ];

        if ($remainingQuantity < $minimumQuantity) {
            $templateId = 'seller_product_outofstock_notification';
            $emailHelper = \Magento\Framework\App\ObjectManager::getInstance()->create(MarketplaceEmailHelper::class);
            $emailHelper->yourCustomMailSendMethod($emailTempVariables, $senderInfo, $receiverInfo, $templateId);
        }
    }
}
?>