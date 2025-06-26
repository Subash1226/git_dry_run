<?php
namespace Quantity\Salable\Plugin;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AddSalableQuantityAttribute
{
    protected $stockRegistry;
    protected $getProductSalableQty;
    protected $logger;
    protected $productRepository;
    private $isProcessing = false;

    public function __construct(
        StockRegistryInterface $stockRegistry,
        GetProductSalableQtyInterface $getProductSalableQty,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    public function afterGetExtensionAttributes(
        ProductInterface $subject, 
        $extensionAttributes
    ) {
        if ($this->isProcessing) {
            return $extensionAttributes;
        }

        try {
            $this->isProcessing = true;

            try {
                $sku = $subject->getSku();
                if (empty($sku)) {
                    $this->isProcessing = false;
                    return $extensionAttributes;
                }

                $product = $this->productRepository->get($sku, false, null, true);
                $typeId = $product->getTypeId();

                if (empty($typeId)) {
                    $this->logger->warning('Product type is missing for SKU: ' . $sku);
                    $this->isProcessing = false;
                    return $extensionAttributes;
                }

            } catch (\Exception $e) {
                $this->logger->error('Error loading product data: ' . $e->getMessage());
                $this->isProcessing = false;
                return $extensionAttributes;
            }

            if (in_array($typeId, ['bundle', 'configurable', 'grouped', 'virtual'])) {
                $this->isProcessing = false;
                return $extensionAttributes;
            }

            if ($extensionAttributes === null) {
                $extensionAttributes = $subject->getExtensionAttributesFactory()->create();
            }

            try {
                $stockId = 1;
                $salableQty = $this->getProductSalableQty->execute($sku, $stockId);
                $extensionAttributes->setSalableQuantity($salableQty);
            } catch (\Exception $e) {
                $this->logger->error('Error getting salable quantity: ' . $e->getMessage());
                $extensionAttributes->setSalableQuantity(0);
            }

            $this->isProcessing = false;
            return $extensionAttributes;

        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->logger->error('Error in AddSalableQuantityAttribute: ' . $e->getMessage());
            return $extensionAttributes;
        }
    }
}