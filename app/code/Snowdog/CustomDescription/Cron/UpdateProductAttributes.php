<?php
namespace Snowdog\CustomDescription\Cron;

use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class UpdateProductAttributes
{
    private $customDescRepo;
    private $timezone;
    private $logger;
    private $productRepository;
    private $stockRegistry;
    private $productCollectionFactory;
    private $state;

    public function __construct(
        CustomDescriptionRepositoryInterface $customDescRepo,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        CollectionFactory $productCollectionFactory,
        State $state
    ) {
        $this->customDescRepo = $customDescRepo;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->state = $state;
    }

    public function execute()
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            $this->logger->error('Error setting area code: ' . $e->getMessage());
        }
        $this->logger->info('Starting product expiry update cron job');

        $currentDate = $this->timezone->date()->format('Y-m-d');
        $products = $this->getProductCollection();
        $this->logger->info('Current Date :  ' . $currentDate);

        foreach ($products as $product) {
            $this->processProduct($product, $currentDate);
        }

        $this->logger->info('Finished product expiry update cron job');
    }

    private function getProductCollection()
    {
        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->load();
    }

    private function processProduct($product, $currentDate)
    {
        $customDescriptions = $this->customDescRepo->getListByProductId($product->getId());

        if (empty($customDescriptions)) {
            return;
        }

        usort($customDescriptions, function($a, $b) {
            $dateA = strtotime($a->getExpiryDate());
            $dateB = strtotime($b->getExpiryDate());
            return $dateA - $dateB;
        });

        $expiryDates = [];
        foreach ($customDescriptions as $description) {
            $expiryDates[] = date('Y-m-d', strtotime($description->getExpiryDate()));
        }

        $this->logger->info('Product ID: ' . $product->getId() . ', Sorted Expiry Dates: ' . implode(', ', $expiryDates));

        $nextValidDescription = null;

        foreach ($customDescriptions as $description) {
            $expiryDate = date('Y-m-d', strtotime($description->getExpiryDate()));
            $expiryStatus = $description->getExpiryStatus();
            if($expiryStatus === '1'){
            $this->logger->info("Quantity Expiry Status = " . $expiryStatus);
            $this->updateQuantityInDescription($description, $product);
            }

            if ($expiryDate < $currentDate || $description->getQty() <= 0) {
                $this->updateStatus($description, 0);
            }  elseif ($nextValidDescription === null) {
                if (!$this->checkAndUpdateQuantity($description, $product)) {
                    $nextValidDescription = $description;
                    $this->updateStatus($description, 1);
                }
            } else {
                $this->updateStatus($description, 2);
            }
        }
        if ($nextValidDescription){
            $this->updateProductData($product, $nextValidDescription);
        }
    }

    private function checkAndUpdateQuantity(CustomDescriptionInterface $description, $product)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $currentQuantity = $stockItem->getQty();

            if ($description->getExpiryStatus() == 1 && ($currentQuantity < 1 || $description->getQty() <= 0)) {
                $this->updateStatus($description, 0);
                $this->logger->info("Updated expiry status to 0 for description ID: " . $description->getId() . " due to low quantity");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error("Error checking and updating quantity: " . $e->getMessage());
            return false;
        }
    }

    private function updateStatus(CustomDescriptionInterface $description, int $newStatus)
    {
        $currentStatus = $description->getExpiryStatus();
        if ($currentStatus != $newStatus) {
            $description->setExpiryStatus($newStatus);
            $this->customDescRepo->save($description);
            $this->logger->info(sprintf('Updated expiry status to %d for description ID: %d', $newStatus, $description->getId()));
        }
    }

    private function updateProductData($product, CustomDescriptionInterface $description)
    {
        try {
            $this->logger->info("Attempting to update product ID: " . $product->getId());
            $quantity = $description->getQty();
            $expiryDate = date('Y-m-d', strtotime($description->getExpiryDate()));
            $specialPriceFromDate = date('Y-m-d', strtotime($description->getSpecialPriceFromDate()));
            $specialPriceToDate = date('Y-m-d', strtotime($description->getSpecialPriceToDate()));

            $detDesc = [
                'price' => $description->getDescription(),
                'special_price' => $description->getPrice(),
                'special_from_date' => $specialPriceFromDate,
                'special_to_date' => $specialPriceToDate,
                'expiry' => $expiryDate
            ];

            $this->logger->info("Price: " . $detDesc['price']);
            $this->logger->info("Special Price: " . $detDesc['special_price']);
            $this->logger->info("Special Price From: " . $detDesc['special_from_date']);
            $this->logger->info("Special Price To: " . $detDesc['special_to_date']);
            $this->logger->info("Expiry: " . $detDesc['expiry']);
            $this->logger->info("Quantity: " . $quantity);

            $this->updateQuantity($product, $quantity);
            $this->updateProductAttributes($product, $detDesc);

            $this->logger->info("About to save product ID: " . $product->getId());
            $this->productRepository->save($product);
            $this->logger->info("Successfully saved product ID: " . $product->getId());
        } catch (\Exception $e) {
            $this->logger->error('Error updating product data for product ID ' . $product->getId() . ': ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    private function updateQuantity($product, $quantity)
    {
        try {
            if (isset($quantity)) {
                $newQuantity = $quantity;
                $product->setQuantityAndStockStatus(['qty' => $newQuantity, 'is_in_stock' => $newQuantity > 0]);
                $this->logger->info("Updated product ID: " . $product->getId() . " with quantity: " . $newQuantity);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error updating quantity: " . $e->getMessage());
        }
    }

    private function updateProductAttributes($product, $detDesc)
    {
        try {
            $this->logger->info("Starting product attribute update for product ID: " . $product->getId());
    
            $attributesToUpdate = [
                'price' => 'price',
                'special_price' => 'special_price',
                'special_from_date' => 'special_from_date',
                'special_to_date' => 'special_to_date',
                'expiry' => 'expiry'
            ];
    
            foreach ($attributesToUpdate as $descKey => $productAttribute) {
                if (isset($detDesc[$descKey])) {
                    $oldValue = $product->getData($productAttribute);
                    $newValue = $descKey === 'special_from_date' || $descKey === 'special_to_date' || $descKey === 'expiry'
                        ? date('Y-m-d 00:00:00', strtotime($detDesc[$descKey]))
                        : $detDesc[$descKey];
    
                    $product->setData($productAttribute, $newValue);
    
                    $this->logger->info(
                        "Updated Product Attribute: {$productAttribute}",
                        [
                            'product_id' => $product->getId(),
                            'old_value' => $oldValue,
                            'new_value' => $newValue
                        ]
                    );
                }
            }
    
            $this->logger->info("Finished product attribute update for product ID: " . $product->getId());
        } catch (\Exception $e) {
            $this->logger->error(
                "Error updating product attributes",
                [
                    'product_id' => $product->getId(),
                    'error_message' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ]
            );
            $this->messageManager->addErrorMessage(__("Error updating product attributes: %1", $e->getMessage()));
        }
    }
    

    private function updateQuantityInDescription(CustomDescriptionInterface $description, $product)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $currentProductQuantity = $stockItem->getQty();
            $descriptionQuantity = $description->getQty();
            $description->setQty($currentProductQuantity);
            $this->customDescRepo->save($description);
            $this->logger->info("Custom description ID: " . $description->getId() .
                                ", Product quantity: " . $currentProductQuantity .
                                ", Description quantity: " . $descriptionQuantity);
        } catch (\Exception $e) {
            $this->logger->error("Error in updateQuantityInDescription: " . $e->getMessage());
        }
    }
}
?>
