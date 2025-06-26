<?php
declare(strict_types=1);

namespace Quantity\Contents\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchResultsInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductRepository
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        TimezoneInterface $timezone
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * Get attribute label for quantity_contents
     *
     * @param mixed $value
     * @return string
     */
    private function getQuantityContentsLabel($value)
    {
        try {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                'quantity_contents'
            );
            
            if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {
                $options = $attribute->getSource()->getAllOptions(false);
                
                foreach ($options as $option) {
                    if ($option['value'] == $value) {
                        return $option['label'];
                    }
                }
                return $value;
            }
            
            return $value;
        } catch (\Exception $e) {
            $this->logger->error('Error getting quantity_contents label: ' . $e->getMessage());
            return $value;
        }
    }

    /**
     * Process special price according to date constraints
     *
     * @param ProductInterface $product
     * @return void
     */
    private function processSpecialPrice(ProductInterface $product)
    {
        try {
            $specialPrice = $product->getSpecialPrice();
            
            if ($specialPrice !== null) {
                $specialToDate = $product->getCustomAttribute('special_to_date');
                
                if ($specialToDate !== null) {
                    $specialToDateTime = $specialToDate->getValue();
                    $currentDateTime = $this->timezone->date()->format('Y-m-d H:i:s');
                    
                    // If special_to_date has passed, set special_price to regular price
                    if ($currentDateTime > $specialToDateTime) {
                        $regularPrice = $product->getPrice();
                        $product->setSpecialPrice($regularPrice);
                        
                        // Update the custom attribute if it exists
                        if ($product->getCustomAttribute('special_price')) {
                            $product->setCustomAttribute('special_price', (string)$regularPrice);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error processing special price: ' . $e->getMessage());
        }
    }

    /**
     * After plugin for get method
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param string $sku
     * @return ProductInterface
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        string $sku
    ) {
        $this->processSpecialPrice($result);
        return $this->processProductQuantityContents($result);
    }

    /**
     * After plugin for getList method
     *
     * @param ProductRepositoryInterface $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        SearchResultsInterface $result
    ) {
        $items = $result->getItems();
        
        foreach ($items as $product) {
            $this->processSpecialPrice($product);
            $this->processProductQuantityContents($product);
        }
        
        return $result;
    }

    /**
     * Process quantity contents for a single product
     *
     * @param ProductInterface $product
     * @return ProductInterface
     */
    private function processProductQuantityContents(ProductInterface $product)
    {
        try {
            $quantityContents = $product->getData('quantity_contents');
            
            if ($quantityContents !== null) {
                $label = $this->getQuantityContentsLabel($quantityContents);
                $product->setCustomAttribute('quantity_contents', $label);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error processing quantity_contents: ' . $e->getMessage());
        }
        
        return $product;
    }
}