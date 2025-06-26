<?php

namespace Price\Extension\Plugin;

use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Api\Data\CartItemExtensionFactory;

class QuotePlugin
{
    /**
     * @var CartItemExtensionFactory
     */
    protected $cartItemExtensionFactory;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * QuotePlugin constructor.
     * @param CartItemExtensionFactory $cartItemExtensionFactory
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CartItemExtensionFactory $cartItemExtensionFactory,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->cartItemExtensionFactory = $cartItemExtensionFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Add custom extension attributes to TotalsInterface
     *
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $subject
     * @param TotalsInterface $totals
     * @return TotalsInterface
     */
    public function afterGet(
        \Magento\Quote\Api\CartTotalRepositoryInterface $subject,
        TotalsInterface $totals
    ) {
        return $this->setAttributeValue($totals);
    }

    /**
     * Set custom extension attributes to TotalsInterface
     *
     * @param TotalsInterface $totals
     * @return TotalsInterface
     */
    private function setAttributeValue(TotalsInterface $totals)
    {
        $items = $totals->getItems();
        foreach ($items as $item) {
            $itemId = $item->getItemId();
           
            $productId = $this->getProductIdByItemId($itemId);
          
            if ($productId) {
                $mrpPrice = $this->getPrice($productId);
                
                $extensionAttributes = $item->getExtensionAttributes() ?? $this->cartItemExtensionFactory->create(); // Use correct factory
           
                $extensionAttributes->setMrpPrice($mrpPrice);
                $item->setExtensionAttributes($extensionAttributes);
            }
        }
        return $totals;
    }

    /**
     * Get product ID from quote item ID
     *
     * @param int $itemId
     * @return int|null
     */
    protected function getProductIdByItemId($itemId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('quote_item');

        $select = $connection->select()->from($tableName, ['product_id'])
            ->where('item_id = ?', $itemId);

        $productId = $connection->fetchOne($select);

        return $productId ? (int) $productId : null;
    }

    /**
     * Get MRP price from database based on product ID
     *
     * @param int $productId
     * @return float|null
     */
    protected function getPrice($productId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_decimal');

        $select = $connection->select()->from($tableName, ['value'])
            ->where('entity_id = ?', $productId);

        $mrpPrice = $connection->fetchOne($select);

        return $mrpPrice ? (int) $mrpPrice : null;
        
    }
}
