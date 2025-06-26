<?php

declare(strict_types=1);

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;
use Snowdog\CustomDescription\Helper\Data;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;
use Snowdog\CustomDescription\Model\Resource\CustomDescriptionBatchProcessor;
use Psr\Log\LoggerInterface;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection as CustomDescriptionCollection;

class ProductSave
{
    private $messageManager;
    private $request;
    private $registry;
    private $productMetadata;
    private $customDescriptionFactory;
    private $customDescRepo;
    private $helper;
    private $productRepository;
    private $descriptionBatchProcessor;
    private $stockRegistry;
    private $logger;

    public function __construct(
        CustomDescriptionBatchProcessor $descBatchProcessor,
        ManagerInterface $messageManager,
        RequestInterface $request,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ProductMetadataInterface $productMetadata,
        CustomDescriptionFactory $customDescFactory,
        CustomDescriptionRepositoryInterface $customDescRepo,
        Data $helper,
        StockRegistryInterface $stockRegistry,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->productMetadata = $productMetadata;
        $this->customDescriptionFactory = $customDescFactory;
        $this->customDescRepo = $customDescRepo;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->descriptionBatchProcessor = $descBatchProcessor;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
    }

    public function afterExecute(Save $subject, Redirect $result): Redirect
    {
        try {
            $product = $this->registry->registry('current_product');
            $params = $this->request->getParams();
            $customDescData = $params['product']['descriptions'] ?? false;

            if (empty($product)) {
                return $result;
            }

            $productId = (int) $product->getId();
            $customDescCollection = $this->customDescRepo->getCustomDescriptionByProductId($productId);
            $customDescCollectionSize = is_array($customDescCollection) ? count($customDescCollection) : $customDescCollection->getSize();
            
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $currentQuantity = $stockItem->getQty();
            $this->messageManager->addSuccessMessage(__("Current product quantity: %1", $currentQuantity));

            if (!is_array($customDescData) && !$customDescCollectionSize) {
                return $result;
            }

            if (!is_array($customDescData)) {
                $this->removeAllItems($customDescCollection);
                return $result;
            }

            if ($customDescCollectionSize) {
                $customDescData = $this->getMappedCustomDescData($customDescData);
                $customDescCollection = $this->getMappedCustomDescCollection($customDescCollection);
                $this->removeToDeleteItems($customDescData, $customDescCollection);
                $this->logger->info('CustomDescData: ' . json_encode($customDescData));
                $this->logger->info('CustomDescCollection keys: ' . json_encode(array_keys($customDescCollection)));
            }

            $last_expiry_status = $this->getLastExpiryStatus($customDescCollection);
            $isFirstRow = $customDescCollectionSize === 0;
            $hasUpdatedProduct = false;

            foreach ($customDescData as $detDesc) {
                if (!$this->validateCustomDescData($detDesc)) {
                    $this->logger->warning('Invalid custom description data', ['data' => $detDesc]);
                    continue;
                }

                $item = $this->initItem($detDesc);

                if ($this->excludeInvalidItem($item, $detDesc)) {
                    continue;
                }

                $currentDate = date('Y-m-d');
                $expiryDate = \DateTime::createFromFormat('m/d/Y', $detDesc['expiry_date'])->format('Y-m-d');

                if ($expiryDate < $currentDate) {
                    $expiry_status = '0';
                } elseif ($expiryDate == $currentDate) {
                    $expiry_status = '0';
                } else {
                    if (empty($detDesc['entity_id'])) {
                        if ($isFirstRow) {
                            $expiry_status = '1';
                        } elseif ($last_expiry_status == 0) {
                            $expiry_status = '1';
                        } else {
                            $expiry_status = '2';
                        }
                    } else {
                        $expiry_status = $detDesc['expiry_status'] ?? '2';
                    }

                    if (!$hasUpdatedProduct) {
                        $this->updateQuantity($product, $detDesc);
                        $this->updateProductAttributes($product, $detDesc);
                        $hasUpdatedProduct = true;
                    }
                }

                $item = $this->setItemData($item, $detDesc, $productId, $expiry_status);
                $this->descriptionBatchProcessor->persist($item);

                $isFirstRow = false;
            }

            $this->descriptionBatchProcessor->flush();
            $this->productRepository->save($product);
            $this->messageManager->addSuccessMessage(__("Product and custom descriptions updated successfully."));

        } catch (\Exception $e) {
            $this->logger->critical('Error in ProductSave plugin: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->messageManager->addErrorMessage(__("Error saving product: %1", $e->getMessage()));
        }

        return $result;
    }

    private function updateQuantity($product, $detDesc)
    {
        try {
            if (isset($detDesc['quantity']) && is_numeric($detDesc['quantity'])) {
                $stockItem = $this->stockRegistry->getStockItem($product->getId());
                $currentQuantity = $stockItem->getQty();
                $newQuantity = (float)$detDesc['quantity'];
                $this->logger->info("Updating quantity from {$currentQuantity} to {$newQuantity}");
                $product->setQuantityAndStockStatus(['qty' => $newQuantity, 'is_in_stock' => $newQuantity > 0]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error updating quantity: " . $e->getMessage());
            $this->messageManager->addErrorMessage(__("Error updating quantity: %1", $e->getMessage()));
        }
    }

    private function updateProductAttributes($product, $detDesc)
    {
        try {
            $this->logger->info("Starting product attribute update for product ID: " . $product->getId());

            $attributesToUpdate = [
                'price' => 'price',
                'special_price' => 'special_price',
                'special_price_from_date' => 'special_from_date',
                'special_price_to_date' => 'special_to_date',
                'expiry_date' => 'expiry'
            ];

            foreach ($attributesToUpdate as $descKey => $productAttribute) {
                if (isset($detDesc[$descKey])) {
                    $oldValue = $product->getData($productAttribute);
                    $newValue = $detDesc[$descKey];

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

    private function validateCustomDescData(array $customDescData): bool
    {
        return !empty($customDescData['price'])
            && !empty($customDescData['title'])
            && (!isset($customDescData['special_price']) || is_numeric($customDescData['special_price']))
            && (!isset($customDescData['quantity']) || is_numeric($customDescData['quantity']))
            && (!isset($customDescData['expiry_date']) || strtotime($customDescData['expiry_date']) !== false)
            && (!isset($customDescData['special_price_from_date']) || strtotime($customDescData['special_price_from_date']) !== false)
            && (!isset($customDescData['special_price_to_date']) || strtotime($customDescData['special_price_to_date']) !== false);
    }

    private function setItemData(
        CustomDescriptionInterface $item,
        array $detDesc,
        int $productId,
        string $expiry_status
    ): CustomDescriptionInterface {
        $sortOrder = $detDesc['position'] ?? 0;

        $item->setData(CustomDescriptionInterface::DESCRIPTION, $detDesc['price']);
        $item->setData(CustomDescriptionInterface::TITLE, $detDesc['title']);
        $item->setData(CustomDescriptionInterface::PRODUCT_ID, $productId);
        $item->setData(CustomDescriptionInterface::POSITION, $sortOrder);
        $item->setData(CustomDescriptionInterface::EXPIRY_STATUS, $expiry_status);

        $optionalFields = [
            'special_price' => CustomDescriptionInterface::SPECIAL_PRICE,
            'quantity' => CustomDescriptionInterface::QUANTITY,
            'expiry_date' => CustomDescriptionInterface::EXPIRY_DATE,
            'special_price_from_date' => CustomDescriptionInterface::SPECIAL_PRICE_FROM_DATE,
            'special_price_to_date' => CustomDescriptionInterface::SPECIAL_PRICE_TO_DATE,
            'purchase_rate' => CustomDescriptionInterface::PURCHASE_RATE,
            'purchase_quantity' => CustomDescriptionInterface::PURCHASE_QUANTITY,
            'expiry_status_option' => CustomDescriptionInterface::FIELD_CUSTOM_STATUS,
            'comments' => CustomDescriptionInterface::FIELD_COMMENTS
        ];

        foreach ($optionalFields as $field => $interface) {
            if (isset($detDesc[$field])) {
                $item->setData($interface, $detDesc[$field]);
            }
        }

        return $item;
    }

    private function initItem(array $detDesc): CustomDescriptionInterface
    {
        if (empty($detDesc['entity_id'])) {
            return $this->customDescriptionFactory->create();
        }
        return $this->customDescRepo->get($detDesc['entity_id']);
    }

    private function excludeInvalidItem(CustomDescriptionInterface $item, array $detDesc): bool
    {
        $requiredKeys = ['title', 'price'];
        foreach ($requiredKeys as $key) {
            if (!isset($detDesc[$key]) || empty($detDesc[$key])) {
                $this->messageManager
                    ->addErrorMessage(__("Invalid description data. Missing or empty required field: %1", $key));
                return true;
            }
        }

        if (empty($item->getId()) && !$this->validateCustomDescData($detDesc)) {
            $this->messageManager
                ->addErrorMessage(__("Invalid description data. Please check all fields."));
            return true;
        }

        return false;
    }

    private function removeAllItems($customDescriptionCollection): void
    {
        foreach ($customDescriptionCollection as $item) {
            $this->updateItemExpiryStatus($item);
        }
    }

    private function removeToDeleteItems(array $customDescData, array $customDescriptionCollection): void
    {
        foreach ($customDescriptionCollection as $item) {
            if (!isset($customDescData[$item->getId()])) {
                $this->updateItemExpiryStatus($item);
            }
        }
    }

    private function updateItemExpiryStatus(CustomDescriptionInterface $item): void
    {
        try {
            $item->setData(CustomDescriptionInterface::EXPIRY_STATUS, 5);
            $this->descriptionBatchProcessor->persist($item);
        } catch (\Exception $e) {
            $this->messageManager
                ->addErrorMessage(__("Couldn't update item expiry status: " . $e->getMessage()));
        }
    }

    private function getMappedCustomDescData(array $customDescData): array
    {
        $data = [];
        foreach ($customDescData as $item) {
            if (isset($item['entity_id'])) {
                $data[$item['entity_id']] = $item;
            }
        }
        return $data;
    }

    private function getMappedCustomDescCollection($customDescCollection): array
    {
        $collection = [];
        if ($customDescCollection instanceof CustomDescriptionCollection) {
            foreach ($customDescCollection as $item) {
                $collection[$item->getId()] = $item;
            }
        } elseif (is_array($customDescCollection)) {
            foreach ($customDescCollection as $item) {
                if ($item instanceof CustomDescriptionInterface) {
                    $collection[$item->getId()] = $item;
                } elseif (isset($item['id'])) {
                    $collection[$item['id']] = $item;
                }
            }
        }
        return $collection;
    }

    private function getLastExpiryStatus(array $customDescCollection): ?int
    {
        $lastStatus = null;
        foreach ($customDescCollection as $item) {
            if ($item instanceof CustomDescriptionInterface) {
                $status = $item->getExpiryStatus();
                if ($status !== null) {
                    $lastStatus = (int)$status;
                }
            } elseif (is_array($item) && isset($item['expiry_status'])) {
                $lastStatus = (int)$item['expiry_status'];
            }
        }
        return $lastStatus;
    }
}