<?php

/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Marketplace
 * @version     1.2
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2017 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */

namespace Apptha\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class Cataloginventoryupdate implements ObserverInterface
{
    protected $action;
    protected $productRepository;
    protected $stockItemRepository;
    protected $scopeConfig;

    public function __construct(
        Action $action,
        ProductRepository $productRepository,
        StockRegistryInterface $stockItemRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->action = $action;
        $this->productRepository = $productRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sellerNotification = $this->scopeConfig->getValue('marketplace/seller/seller_lowstock');
        $minimumQuantity = $this->scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($sellerNotification == "1") {
            $product = $observer->getProduct();
            $productType = $product->getTypeId();

            if ($productType == 'configurable') {
                $data = $product->getTypeInstance()->getConfigurableOptions($product);
                $options = [];
                foreach ($data as $attr) {
                    foreach ($attr as $p) {
                        $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
                    }
                }
                foreach ($options as $sku => $d) {
                    $pr = $this->productRepository->get($sku);
                    $product = $this->productRepository->getById($pr->getId());
                    $inventory = $this->stockItemRepository->getStockItem($pr->getId());
                    $inStock = $inventory->getIsInStock();
                    $quantity = $inventory->getQty();
                    $productSellerId = $product->getSellerId();

                    if ($pr->getId() && $quantity < $minimumQuantity) {
                        $this->logProductId($pr->getId());

                        $seller = $this->loadCustomer($pr->getId());
                        $admin = $this->getAdminHelper();
                        $adminName = $admin->getAdminName();
                        $adminEmail = $admin->getAdminEmail();

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
                            'sku' => $product->getSku(),
                            'productname' => $product->getName()
                        ];
                        $sellerTemplateId = $this->getTemplate($quantity, $inStock, $minimumQuantity);
                        $this->getEmailHelper()->yourCustomMailSendMethod($emailTempVariables, $senderInfo, $receiverInfo, $sellerTemplateId);
                    }
                }
            } else {
                $productId = $product->getId();
                $product = $this->productRepository->getById($productId);
                $inventory = $this->stockItemRepository->getStockItem($productId);
                $inStock = $inventory->getIsInStock();
                $quantity = $inventory->getQty();
                $productSellerId = $product->getSellerId();

                if ($productSellerId && $quantity < $minimumQuantity) {
                    $seller = $this->loadCustomer($productSellerId);
                    $admin = $this->getAdminHelper();
                    $adminName = $admin->getAdminName();
                    $adminEmail = $admin->getAdminEmail();

                    $receiverInfo = [
                        'name' => $seller->getName(),
                        'email' => $seller->getEmail()
                    ];
                    $senderInfo = [
                        'name' => $adminName,
                        'email' => $adminEmail
                    ];
                    $emailTempVariables = [
                        'sku' => $product->getSku(),
                        'receivername' => $seller->getName(),
                        'productname' => $product->getName(),
                        'qty' => $quantity,
                        'minqty' => $minimumQuantity
                    ];
                    $sellerTemplateId = $this->getTemplate($quantity, $inStock, $minimumQuantity);
                    if ($sellerTemplateId != "") {
                        $this->getEmailHelper()->yourCustomMailSendMethod($emailTempVariables, $senderInfo, $receiverInfo, $sellerTemplateId);
                    }
                }
            }
        }
    }

    protected function getTemplate($quantity, $inStock, $minimumQuantity)
    {
        if ($quantity == '0' || $inStock == "") {
            return 'seller_product_notification';
        } else if ($quantity < $minimumQuantity && $quantity != '0') {
            return 'seller_product_outofstock_notification';
        } else {
            exit;
        }
    }

    protected function logProductId($productId)
    {
        $writer = new Stream(BP . '/var/log/test.log');
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($productId, true));
    }

    protected function loadCustomer($customerId)
    {
        return $this->productRepository->getById($customerId); // Adjust this line if necessary to load the customer properly
    }

    protected function getAdminHelper()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Apptha\Marketplace\Helper\Data');
    }

    protected function getEmailHelper()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Apptha\Marketplace\Helper\Email');
    }
}
