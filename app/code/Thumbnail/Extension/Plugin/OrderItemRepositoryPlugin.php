<?php

namespace Thumbnail\Extension\Plugin;

use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class OrderItemRepositoryPlugin
 */
class OrderItemRepositoryPlugin
{
    protected $orderItemExtensionFactory;
    protected $productFactory;

    /**
     * OrderItemRepositoryPlugin constructor
     *
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(    
        OrderItemExtensionFactory $orderItemExtensionFactory,
        ProductFactory $productFactory)
    {
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        $customAttribute = $orderItem->getData('product_attribute');
 
        $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->orderItemExtensionFactory->create();
        $extensionAttributes->setBuspack($customAttribute);
        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $searchResult
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $order = $this->getOrderItemExtensionAttributes($order);
        }

        return $searchResult;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    protected function getOrderItemExtensionAttributes(OrderItemInterface $orderItem)
    {
        $product = $this->productFactory->create();
        $product->load($orderItem->getProductId());
        $customAttribute = $product->getThumbnail();
        if ($customAttribute) {
            $extensionAttributes = $orderItem->getExtensionAttributes() ?: $this->orderItemExtensionFactory->create();
            $extensionAttributes->setThumbnail($customAttribute);
            $orderItem->setExtensionAttributes($extensionAttributes);
        }
        
        return $orderItem;
    }
}
