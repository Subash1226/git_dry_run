<?php
/**
 * Category: Wishusucess_CartImages
 * Developer: Hemant Singh Magento 2x Developer
 * Website: http://wwww.wishusucess.com
 */
namespace Wishusucess\CartImages\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class QuotePlugin
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionFactory
     */
    protected $cartItemExtension;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        TimezoneInterface $timezone
    ) {
        $this->cartItemExtension = $cartItemExtension;
        $this->productRepository = $productRepository;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->timezone = $timezone;
    }

    /**
     * Add attribute values
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param $quote
     * @return $quoteData
     */
    public function afterGet(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * Add attribute values
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param $quote
     * @return $quoteData
     */
    public function afterGetActiveForCustomer(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * Set value of attributes
     *
     * @param $quote
     * @return $quote
     */
    private function setAttributeValue($quote)
    {
        if ($quote->getItemsCount()) {
            foreach ($quote->getItems() as $item) {
                $extensionAttributes = $item->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->cartItemExtension->create();
                }

                $productData = $this->productRepository->create()->get($item->getSku());
                
                $price = $productData->getPrice();
                $specialPrice = $this->getValidSpecialPrice($productData);
                
                $extensionAttributes->setSpecialPrice($specialPrice);
                $extensionAttributes->setPrice($price);
                $prescription = $productData->getData('prescription_check');
                $extensionAttributes->setPrescription($prescription);
                $extensionAttributes->setImage($productData->getThumbnail());

                $stockId = 1;
                $salableQty = $this->getProductSalableQty->execute($item->getSku(), $stockId);
                $extensionAttributes->setSalableQuantity($salableQty);

                $item->setExtensionAttributes($extensionAttributes);
            }
        }

        return $quote;
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
        
        // If there's no special price, just return null or regular price
        if ($specialPrice === null || $specialPrice === '') {
            return $product->getPrice();
        }
        
        $currentDate = $this->timezone->date()->format('Y-m-d H:i:s');
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        
        // Check if special price is currently valid
        $isValidFrom = (!$specialFromDate || $currentDate >= $specialFromDate);
        $isValidTo = (!$specialToDate || $currentDate <= $specialToDate);
        
        // If the special price is valid (within date range), return it
        if ($isValidFrom && $isValidTo) {
            return $specialPrice;
        }
        
        // Special price is not valid or has expired, return regular price
        return $product->getPrice();
    }
}