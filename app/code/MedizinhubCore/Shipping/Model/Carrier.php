<?php
namespace MedizinhubCore\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'medizinhubshipping';
    protected $_rateResultFactory;
    protected $_rateMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        
        $this->_logger->info('MedizinhubShipping: Carrier initialized', [
            'carrier_code' => $this->_code
        ]);
    }

    private function calculateProductWeight($request)
    {
        $this->_logger->info('MedizinhubShipping: Starting weight calculation');
        
        $totalWeight = 0;
        $itemDetails = [];
        
        // Get configurable weight values
        $packagingWeight = (float)$this->getConfigData('packaging_weight') ?: 20;
        $stripBaseWeight = (float)$this->getConfigData('strip_base_weight') ?: 100;
        $singleProductDefaultWeight = (float)$this->getConfigData('single_product_default_weight') ?: 200;
        $multipleProductDefaultWeight = (float)$this->getConfigData('multiple_product_default_weight') ?: 100;
        
        // Calculate total product count first
        $totalProductCount = 0;
        foreach ($request->getAllItems() as $item) {
            $totalProductCount += $item->getQty();
        }
        
        $this->_logger->debug('MedizinhubShipping: Weight configuration loaded', [
            'packaging_weight' => $packagingWeight,
            'strip_base_weight' => $stripBaseWeight,
            'single_product_default_weight' => $singleProductDefaultWeight,
            'multiple_product_default_weight' => $multipleProductDefaultWeight,
            'total_product_count' => $totalProductCount
        ]);
        
        foreach ($request->getAllItems() as $item) {
            $productName = $item->getName();
            $qty = $item->getQty();
            
            $this->_logger->debug('MedizinhubShipping: Processing item', [
                'product_name' => $productName,
                'quantity' => $qty,
                'product_id' => $item->getProductId()
            ]);
            
            // Extract weight from product name (gm or ml)
            $extractedWeight = $this->extractWeightFromName($productName);
            
            $itemWeight = 0;
            $weightSource = '';
            
            if ($extractedWeight > 0) {
                // Weight found in name, use it + packaging weight per product
                $itemWeight = ($extractedWeight + $packagingWeight) * $qty;
                $weightSource = 'extracted_from_name';
                $totalWeight += $itemWeight;
                
                $this->_logger->debug('MedizinhubShipping: Weight extracted from name', [
                    'product_name' => $productName,
                    'extracted_weight_g' => $extractedWeight,
                    'packaging_weight_g' => $packagingWeight,
                    'total_item_weight_g' => $itemWeight,
                    'quantity' => $qty
                ]);
            } else {
                // Check if it's a strip (category_check = 39)
                $product = $item->getProduct();
                $categoryCheck = null;
                
                if ($product) {
                    if ($categoryCheck === null) {
                        try {
                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
                            $fullProduct = $productRepository->getById($product->getId());
                            $categoryCheck = $fullProduct->getData('category_check');
                        } catch (\Exception $e) {
                            $this->_logger->warning('MedizinhubShipping: Could not load full product', [
                                'product_id' => $product->getId(),
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                
                $this->_logger->debug('MedizinhubShipping: No weight in name, checking category', [
                    'product_name' => $productName,
                    'product_id' => $product ? $product->getId() : 'null',
                    'category_check' => $categoryCheck,
                    'category_check_type' => gettype($categoryCheck)
                ]);
                
                // Convert to integer for comparison and handle null/empty values
                $categoryCheckInt = (int)$categoryCheck;
                
                if ($categoryCheck == 39) {
                    // Strip product: strip base weight + packaging weight per product
                    $itemWeight = ($stripBaseWeight + $packagingWeight) * $qty;
                    $weightSource = 'strip_category';
                    $totalWeight += $itemWeight;
                    
                    $this->_logger->debug('MedizinhubShipping: Strip product detected', [
                        'product_name' => $productName,
                        'base_weight_g' => $stripBaseWeight,
                        'packaging_weight_g' => $packagingWeight,
                        'total_item_weight_g' => $itemWeight,
                        'quantity' => $qty
                    ]);
                } else {
                    // Default logic based on total product count
                    if ($totalProductCount == 1) {
                        // Single product: single product default weight + packaging weight
                        $itemWeight = $singleProductDefaultWeight + $packagingWeight;
                        $weightSource = 'single_product_default';
                        $totalWeight += $itemWeight;
                        
                        $this->_logger->debug('MedizinhubShipping: Single product default weight', [
                            'product_name' => $productName,
                            'base_weight_g' => $singleProductDefaultWeight,
                            'packaging_weight_g' => $packagingWeight,
                            'total_item_weight_g' => $itemWeight,
                            'total_product_count' => $totalProductCount
                        ]);
                    } else {
                        // Multiple products: multiple product default weight + packaging weight per product
                        $itemWeight = ($multipleProductDefaultWeight + $packagingWeight) * $qty;
                        $weightSource = 'multiple_product_default';
                        $totalWeight += $itemWeight;
                        
                        $this->_logger->debug('MedizinhubShipping: Multiple product default weight', [
                            'product_name' => $productName,
                            'base_weight_g' => $multipleProductDefaultWeight,
                            'packaging_weight_g' => $packagingWeight,
                            'total_item_weight_g' => $itemWeight,
                            'quantity' => $qty,
                            'total_product_count' => $totalProductCount
                        ]);
                    }
                }
            }
            
            $itemDetails[] = [
                'name' => $productName,
                'quantity' => $qty,
                'weight_g' => $itemWeight,
                'source' => $weightSource
            ];
        }
        
        $this->_logger->info('MedizinhubShipping: Weight calculation completed', [
            'total_weight_g' => $totalWeight,
            'total_product_count' => $totalProductCount,
            'item_details' => $itemDetails
        ]);
        
        return $totalWeight; // Weight in grams
    }

    private function extractWeightFromName($productName)
    {
        $originalName = $productName;
        $productName = strtolower(trim($productName));
        
        $this->_logger->info('MedizinhubShipping: Extracting weight from product name', [
            'original_name' => $originalName,
            'normalized_name' => $productName
        ]);
        
        // Regex patterns for different weight/volume formats
        $patterns = [
            // Weight patterns
            '/(\d+(?:\.\d+)?)\s*(kg|kgs|kilogram|kilograms)\b/i',  // 1kg, 2.5kg, 1 kg
            '/\b(\d+(?:\.\d+)?)\s*(g(?![a-zA-Z])|gm|gms|gram|grams)\b/i',        // 200gm, 200g, 200 gm
            
            // Volume patterns (convert to approximate weight)
            '/(\d+(?:\.\d+)?)\s*(ml|milliliter|milliliters)\b/i',   // 100ml, 250ml
            '/(\d+(?:\.\d+)?)\s*(l|liter|liters|litre|litres)\b/i', // 1l, 2.5l
        ];
        
        foreach ($patterns as $patternIndex => $pattern) {
            if (preg_match($pattern, $productName, $matches)) {
                $value = (float)$matches[1];
                $unit = strtolower(trim($matches[2]));
                
                $convertedWeight = $this->convertToKg($value, $unit);
                
                $this->_logger->info('MedizinhubShipping: Weight pattern matched', [
                    'pattern_index' => $patternIndex,
                    'matched_value' => $value,
                    'matched_unit' => $unit,
                    'converted_weight_kg' => $convertedWeight,
                    'converted_weight_g' => $convertedWeight * 1000
                ]);
                
                return $convertedWeight;
            }
        }
        
        $this->_logger->info('MedizinhubShipping: No weight pattern found in product name', [
            'product_name' => $originalName
        ]);
        
        return 0; // No weight found in name
    }
    
    /**
     * Convert different units to grams (not kg as the method name suggests)
     */
    private function convertToKg($value, $unit)
    {
        $this->_logger->debug('MedizinhubShipping: Converting unit to grams', [
            'value' => $value,
            'unit' => $unit
        ]);
        
        $convertedWeight = 0;
        
        switch ($unit) {
            // Weight units
            case 'kg':
            case 'kgs':
            case 'kilogram':
            case 'kilograms':
                $convertedWeight = $value * 1000; // Convert kg to grams
                break;
                
            case 'g':
            case 'gm':
            case 'gms':
            case 'gram':
            case 'grams':
                $convertedWeight = $value; // Already in grams
                break;
                
            // Volume units (approximate weight - assuming density ~1g/ml for liquids)
            case 'ml':
            case 'milliliter':
            case 'milliliters':
                $convertedWeight = $value; // 1ml ≈ 1g
                break;
                
            case 'l':
            case 'liter':
            case 'liters':
            case 'litre':
            case 'litres':
                $convertedWeight = $value * 1000; // 1L ≈ 1000g
                break;
                
            default:
                $this->_logger->warning('MedizinhubShipping: Unknown unit encountered', [
                    'unit' => $unit,
                    'value' => $value
                ]);
                $convertedWeight = 0;
                break;
        }
        
        $this->_logger->debug('MedizinhubShipping: Unit conversion completed', [
            'original_value' => $value,
            'original_unit' => $unit,
            'converted_grams' => $convertedWeight
        ]);
        
        return $convertedWeight;
    }

    private function calculateWeightBasedShippingFee($state, $totalWeightGrams, $postcode, $subtotal)
    {
        $this->_logger->info('MedizinhubShipping: Calculating weight-based shipping fee', [
            'state' => $state,
            'total_weight_g' => $totalWeightGrams,
            'postcode' => $postcode
        ]);
        
        // Convert grams to weight categories
        $weightInGrams = $totalWeightGrams;
        
        // Special pincode rate (keeping original logic)
        $specialPincodes = explode(',', $this->getConfigData('special_pincode'));        
        if (in_array($postcode, $specialPincodes)) {
            $threshold = (float)$this->getConfigData('special_pincode_threshold');
            if ($subtotal >= $threshold) {
                $rate = (float)$this->getConfigData('special_pincode_above_rate');
                $this->_logger->info('MedizinhubShipping: Special pincode above threshold rate', [
                    'postcode' => $postcode,
                    'subtotal' => $subtotal,
                    'threshold' => $threshold,
                    'rate' => $rate
                ]);
            } else {
                $rate = (float)$this->getConfigData('special_pincode_rate');
                $this->_logger->info('MedizinhubShipping: Special pincode below threshold rate', [
                    'postcode' => $postcode,
                    'subtotal' => $subtotal,
                    'threshold' => $threshold,
                    'rate' => $rate
                ]);
            }
            return $rate;
        }

        $rate = 0;
        $rateSource = '';

        // Tamil Nadu weight-based rates
        if ($state === 'TN') {
            $rateSource = 'tamil_nadu';
            if ($weightInGrams <= 200) {
                $rate = (float)$this->getConfigData('tn_weight_tier1'); // 49
                $tier = 'tier1_0-200g';
            } elseif ($weightInGrams <= 500) {
                $rate = (float)$this->getConfigData('tn_weight_tier2'); // 79
                $tier = 'tier2_201-500g';
            } elseif ($weightInGrams <= 1000) {
                $rate = (float)$this->getConfigData('tn_weight_tier3'); // 119
                $tier = 'tier3_501-1000g';
            } else {
                $rate = (float)$this->getConfigData('tn_weight_tier4'); // 119
                $tier = 'tier4_1000g+';
            }
            
            $this->_logger->info('MedizinhubShipping: Tamil Nadu rate applied', [
                'tier' => $tier,
                'rate' => $rate,
                'weight_g' => $weightInGrams
            ]);
        }
        // Border states weight-based rates
        elseif (in_array($state, explode(',', $this->getConfigData('border_states')))) {
            $rateSource = 'border_states';
            if ($weightInGrams <= 200) {
                $rate = (float)$this->getConfigData('border_weight_tier1'); // 79
                $tier = 'tier1_0-200g';
            } elseif ($weightInGrams <= 500) {
                $rate = (float)$this->getConfigData('border_weight_tier2'); // 99
                $tier = 'tier2_201-500g';
            } elseif ($weightInGrams <= 1000) {
                $rate = (float)$this->getConfigData('border_weight_tier3'); // 159
                $tier = 'tier3_501-1000g';
            } else {
                $rate = (float)$this->getConfigData('border_weight_tier4'); // 199
                $tier = 'tier4_1000g+';
            }
            
            $this->_logger->info('MedizinhubShipping: Border state rate applied', [
                'tier' => $tier,
                'rate' => $rate,
                'weight_g' => $weightInGrams
            ]);
        }
        // Other states weight-based rates
        elseif (in_array($state, explode(',', $this->getConfigData('other_states')))) {
            $rateSource = 'other_states';
            if ($weightInGrams <= 200) {
                $rate = (float)$this->getConfigData('other_weight_tier1'); // 99
                $tier = 'tier1_0-200g';
            } elseif ($weightInGrams <= 500) {
                $rate = (float)$this->getConfigData('other_weight_tier2'); // 119
                $tier = 'tier2_201-500g';
            } elseif ($weightInGrams <= 1000) {
                $rate = (float)$this->getConfigData('other_weight_tier3'); // 179
                $tier = 'tier3_501-1000g';
            } else {
                $rate = (float)$this->getConfigData('other_weight_tier4'); // 249
                $tier = 'tier4_1000g+';
            }
            
            $this->_logger->info('MedizinhubShipping: Other state rate applied', [
                'tier' => $tier,
                'rate' => $rate,
                'weight_g' => $weightInGrams
            ]);
        }
        // Default rate for any remaining states
        else {
            $rate = (float)$this->getConfigData('default_rate');
            $rateSource = 'default';
            
            $this->_logger->info('MedizinhubShipping: Default rate applied', [
                'rate' => $rate,
                'weight_g' => $weightInGrams,
                'state' => $state
            ]);
        }

        $this->_logger->info('MedizinhubShipping: Final shipping fee calculated', [
            'rate_source' => $rateSource,
            'final_rate' => $rate,
            'state' => $state,
            'weight_g' => $weightInGrams,
            'postcode' => $postcode
        ]);

        return $rate;
    }

    public function collectRates(DataObject $request)
    {
        $this->_logger->info('MedizinhubShipping: Starting rate collection');
        
        if (!$this->isActive()) {
            $this->_logger->info('MedizinhubShipping: Carrier is not active, returning false');
            return false;
        }

        try {
            $result = $this->_rateResultFactory->create();
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $subtotal = $request->getBaseSubtotalInclTax() ?: $request->getPackageValue();
            $state = $request->getDestRegionCode();
            $postcode = $request->getDestPostcode();

            $this->_logger->info('MedizinhubShipping: Request details', [
                'subtotal' => $subtotal,
                'state' => $state,
                'postcode' => $postcode,
                'dest_country' => $request->getDestCountryId(),
                'dest_city' => $request->getDestCity()
            ]);

            // Use weight-based calculation
            $totalWeight = $this->calculateProductWeight($request);
            $shippingPrice = $this->calculateWeightBasedShippingFee($state, $totalWeight, $postcode ,$subtotal);

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);

            $this->_logger->info('MedizinhubShipping: Rate collection completed successfully', [
                'shipping_price' => $shippingPrice,
                'total_weight_g' => $totalWeight,
                'carrier_title' => $this->getConfigData('title'),
                'method_title' => $this->getConfigData('name')
            ]);

            return $result;
            
        } catch (\Exception $e) {
            $this->_logger->error('MedizinhubShipping: Error during rate collection', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            // Return error result
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage('Unable to calculate shipping rate. Please try again.');
            
            $result = $this->_rateResultFactory->create();
            $result->append($error);
            
            return $result;
        }
    }

    public function getAllowedMethods()
    {
        $methods = [$this->_code => $this->getConfigData('name')];
        
        $this->_logger->info('MedizinhubShipping: Getting allowed methods', [
            'methods' => $methods
        ]);
        
        return $methods;
    }
}