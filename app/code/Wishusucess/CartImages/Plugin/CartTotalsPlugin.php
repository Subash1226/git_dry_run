<?php
namespace Wishusucess\CartImages\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CartTotalsPlugin
{
    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory
     */
    protected $totalsItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param CartRepositoryInterface $quoteRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        CartRepositoryInterface $quoteRepository,
        TimezoneInterface $timezone
    ) {
        $this->totalsItemExtension = $totalsItemExtension;
        $this->productRepository = $productRepository;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->quoteRepository = $quoteRepository;
        $this->timezone = $timezone;
    }

    /**
     * Add extension attributes to cart totals
     *
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $quoteTotals
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function afterGet(
        \Magento\Quote\Api\CartTotalRepositoryInterface $subject,
        $quoteTotals,
        $cartId
    ) {
        try {
            $quote = $this->quoteRepository->get($cartId);
            $items = $quoteTotals->getItems();
            
            if ($items) {
                foreach ($items as $totalsItem) {
                    $extensionAttributes = $totalsItem->getExtensionAttributes();
                    if ($extensionAttributes === null) {
                        $extensionAttributes = $this->totalsItemExtension->create();
                    }

                    // Find matching quote item
                    foreach ($quote->getAllItems() as $quoteItem) {
                        if ($quoteItem->getItemId() == $totalsItem->getItemId()) {
                            try {
                                $productData = $this->productRepository->create()->get($quoteItem->getSku());
                                
                                $price = $productData->getPrice();                                
                                $specialPrice = $this->getValidSpecialPrice($productData);
                                
                                $extensionAttributes->setSpecialPrice($specialPrice);
                                $extensionAttributes->setPrice($price);
                                $extensionAttributes->setPrescription($productData->getData('prescription_check'));
                                $extensionAttributes->setImage($productData->getThumbnail());
                                $extensionAttributes->setSku($quoteItem->getSku()); // Added SKU

                                $stockId = 1;
                                $salableQty = $this->getProductSalableQty->execute($quoteItem->getSku(), $stockId);
                                $extensionAttributes->setSalableQuantity($salableQty);

                                $totalsItem->setExtensionAttributes($extensionAttributes);
                            } catch (\Exception $e) {
                                continue;
                            }
                            break;
                        }
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            // Quote not found, skip processing
        }

        return $quoteTotals;
    }
    
    /**
     * Get valid special price based on date range
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return float|null
     */
    private function getValidSpecialPrice($product)
    {
        $specialPrice = $product->getData('special_price');
        
        if ($specialPrice === null || $specialPrice === '') {
            return $product->getPrice();
        }
        
        $currentDate = $this->timezone->date()->format('Y-m-d H:i:s');
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        
        $isValidFrom = (!$specialFromDate || $currentDate >= $specialFromDate);
        $isValidTo = (!$specialToDate || $currentDate <= $specialToDate);
        
        if ($isValidFrom && $isValidTo) {
            return $specialPrice;
        }
        
        return $product->getPrice();
    }
}