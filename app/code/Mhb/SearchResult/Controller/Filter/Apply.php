<?php
namespace Mhb\SearchResult\Controller\Filter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;

class Apply extends Action
{
    protected $resultPageFactory;
    protected $productCollectionFactory;
    protected $resultJsonFactory;
    protected $logger;
    protected $pricingHelper;
    protected $imageHelper;
    protected $resourceConnection;
    protected $productFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $productCollectionFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        PricingHelper $pricingHelper,
        ImageHelper $imageHelper,
        ResourceConnection $resourceConnection,
        ProductFactory $productFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->pricingHelper = $pricingHelper;
        $this->imageHelper = $imageHelper;
        $this->resourceConnection = $resourceConnection;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        // $this->logger->info('Apply controller executed with request: ' . json_encode($this->getRequest()->getParams()));
    
        // Retrieve request parameters
        $categoryIds = $this->getRequest()->getParam('category', []);
        $manufacturers = $this->getRequest()->getParam('manufacturer', []);
        $prescriptions = $this->getRequest()->getParam('prescription_check', []);
        $priceMin = $this->getRequest()->getParam('price_min', $this->getDefaultPriceMin());
        $priceMax = $this->getRequest()->getParam('price_max', $this->getDefaultPriceMax());
        $priceRanges = $this->getRequest()->getParam('price_ranges', []); // Add this line
        $searchQuery = $this->getRequest()->getParam('q', '');
        $sortField = $this->getRequest()->getParam('sort_field', 'price');
        $sortDirection = $this->getRequest()->getParam('sort_direction', 'desc');
        $page = (int)$this->getRequest()->getParam('page', 1);
        $pageSize = (int)$this->getRequest()->getParam('page_size', 12);
    
        try {
            // Get direct database connection
            $connection = $this->resourceConnection->getConnection();
            
            // Build the SQL query directly
            $select = $this->buildDirectProductQuery(
                $connection,
                $categoryIds,
                $manufacturers,
                $prescriptions,
                $priceMin,
                $priceMax,
                $priceRanges, // Add this parameter
                $searchQuery,
                $sortField,
                $sortDirection
            );
            
            // Apply pagination
            $select->limitPage($page, $pageSize);
            
            // Log the query for debugging
            // $this->logger->info('Direct SQL Query: ' . $select->__toString());
            
            // Execute the query
            $productIds = $connection->fetchCol($select);
            // $this->logger->info('Found product IDs: ' . implode(',', $productIds));
            
            // Load product collection based on IDs
            $collection = $this->productCollectionFactory->create();
            if (!empty($productIds)) {
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
                $collection->addAttributeToSelect('*');
                
                // Add price data
                $collection->addPriceData();
                
                // Preserve the order from our custom query
                $orderString = "FIELD(e.entity_id, " . implode(',', $productIds) . ")";
                $collection->getSelect()->order(new \Zend_Db_Expr($orderString));
            } else {
                $collection->addFieldToFilter('entity_id', 0);
            }
            
            // Get total count (without pagination)
            $countSelect = clone $select;
            $countSelect->reset(\Zend_Db_Select::COLUMNS);
            $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->columns('COUNT(DISTINCT e.entity_id)');
            $totalCount = $connection->fetchOne($countSelect);
            
            // $this->logger->info('Total count without pagination: ' . $totalCount);
            // $this->logger->info('Collection count after loading: ' . $collection->getSize());

            // Create the result page and block
            $resultPage = $this->resultPageFactory->create();
            $layout = $resultPage->getLayout();

            $block = $layout->createBlock(\Mhb\SearchResult\Block\Filter::class, 'search_result_list');
            $block->setTemplate('Mhb_SearchResult::product/list.phtml');
            $block->setProductCollection($collection);

            // Set data for pagination
            $block->setData('page', $page);
            $block->setData('totalPages', ceil($totalCount / $pageSize));

            $block->setData('pricing_helper', $this->pricingHelper);
            $block->setData('image_helper', $this->imageHelper);
            $block->setData('search_query', $searchQuery);

            $html = $block->toHtml();

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'success' => true,
                'html' => $html,
                'count' => (int)$totalCount,
                'page' => $page,
                'totalPages' => ceil($totalCount / $pageSize)
            ]);
        } catch (\Exception $e) {
            $this->logger->critical('Error in Apply controller: ' . $e->getMessage(), ['exception' => $e]);
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function buildDirectProductQuery(
        $connection,
        $categoryIds,
        $manufacturers,
        $prescriptions,
        $priceMin,
        $priceMax,
        $priceRanges, // Add this parameter
        $searchQuery,
        $sortField,
        $sortDirection
    ) {
        // Get table names and setup base query (unchanged)
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $categoryProductTable = $this->resourceConnection->getTableName('catalog_category_product');
        $priceTable = $this->resourceConnection->getTableName('catalog_product_entity_decimal');
        $varcharTable = $this->resourceConnection->getTableName('catalog_product_entity_varchar');
        $intTable = $this->resourceConnection->getTableName('catalog_product_entity_int');
        $productWebsiteTable = $this->resourceConnection->getTableName('catalog_product_website');
        
        // Get attribute IDs
        $attributeNames = ['name', 'manufacturer', 'price', 'prescription_check', 'sku'];
        $attributeIds = [];
        
        foreach ($attributeNames as $attributeName) {
            $attributeId = $this->getAttributeId($attributeName);
            if ($attributeId) {
                $attributeIds[$attributeName] = $attributeId;
            }
        }
        
        // Start building query
        $select = $connection->select()
            ->from(['e' => $productTable], ['entity_id'])
            ->joinInner(
                ['pw' => $productWebsiteTable],
                'pw.product_id = e.entity_id',
                []
            )
            ->where('pw.website_id = ?', 1); // Assuming website_id 1 for main website
        
        // Join for name attribute
        if (isset($attributeIds['name'])) {
            $select->joinLeft(
                ['name_t' => $varcharTable],
                "name_t.entity_id = e.entity_id AND name_t.attribute_id = {$attributeIds['name']} AND name_t.store_id = 0",
                []
            );
        }
        
        // Join for SKU attribute (if needed)
        if (isset($attributeIds['sku'])) {
            $select->joinLeft(
                ['sku_t' => $varcharTable],
                "sku_t.entity_id = e.entity_id AND sku_t.attribute_id = {$attributeIds['sku']} AND sku_t.store_id = 0",
                []
            );
        }
        
        // Join for price attribute
        if (isset($attributeIds['price'])) {
            $select->joinLeft(
                ['price_t' => $priceTable],
                "price_t.entity_id = e.entity_id AND price_t.attribute_id = {$attributeIds['price']} AND price_t.store_id = 0",
                []
            );
        }
        
        // MODIFIED LOGIC: Only apply search query if no category or manufacturer filters are active
        $hasCategoryFilter = !empty($categoryIds);
        $hasManufacturerFilter = !empty($manufacturers);
        
        if (!empty($searchQuery) && !($hasCategoryFilter || $hasManufacturerFilter)) {
            $likeExpr = '%' . $connection->quote($searchQuery) . '%';
            $likeExpr = str_replace("'", "", $likeExpr);
            
            $conditions = [];
            if (isset($attributeIds['name'])) {
                $conditions[] = $connection->quoteInto('name_t.value LIKE ?', '%' . $searchQuery . '%');
            }
            if (isset($attributeIds['sku'])) {
                $conditions[] = $connection->quoteInto('sku_t.value LIKE ?', '%' . $searchQuery . '%');
            }
            
            if (!empty($conditions)) {
                $select->where('(' . implode(' OR ', $conditions) . ')');
            }
        }
        
        // Category filter
        if ($hasCategoryFilter) {
            $select->joinInner(
                ['cp' => $categoryProductTable],
                'cp.product_id = e.entity_id',
                []
            )
            ->where('cp.category_id IN (?)', $categoryIds);
        }
        
        // Manufacturer filter
        if ($hasManufacturerFilter && isset($attributeIds['manufacturer'])) {
            $select->joinInner(
                ['manufacturer_t' => $varcharTable],
                "manufacturer_t.entity_id = e.entity_id AND manufacturer_t.attribute_id = {$attributeIds['manufacturer']} AND manufacturer_t.store_id = 0",
                []
            )
            ->where('manufacturer_t.value IN (?)', $manufacturers);
        }
        
        // Prescription filter
        if (!empty($prescriptions) && isset($attributeIds['prescription_check'])) {
            $select->joinInner(
                ['prescription_t' => $intTable],
                "prescription_t.entity_id = e.entity_id AND prescription_t.attribute_id = {$attributeIds['prescription_check']} AND prescription_t.store_id = 0",
                []
            )
            ->where('prescription_t.value IN (?)', $prescriptions);
        }
        
        // Modified: Price filter handling with price_ranges
        if (isset($attributeIds['price'])) {
            // Check if we have price ranges
            if (!empty($priceRanges)) {
                $priceConditions = [];
                
                foreach ($priceRanges as $range) {
                    list($min, $max) = explode('-', $range);
                    $minValue = $connection->quote($min);
                    $maxValue = $connection->quote($max);
                    $priceConditions[] = "(price_t.value >= $minValue AND price_t.value <= $maxValue)";
                }
                
                if (!empty($priceConditions)) {
                    $select->where('(' . implode(' OR ', $priceConditions) . ')');
                }
            } else {
                // Fall back to standard min/max if no ranges are specified
                if ($priceMin !== null && $priceMax !== null) {
                    $select->where('price_t.value >= ?', $priceMin)
                          ->where('price_t.value <= ?', $priceMax);
                }
            }
        }
        
        // Apply sorting
        if (isset($attributeIds[$sortField])) {
            // Use the appropriate attribute table
            $attrType = $this->getAttributeType($sortField);
            $tableAlias = $sortField . '_sort';
            $tableType = ($attrType == 'decimal') ? $priceTable : $varcharTable;
            
            $select->joinLeft(
                [$tableAlias => $tableType],
                "$tableAlias.entity_id = e.entity_id AND $tableAlias.attribute_id = {$attributeIds[$sortField]} AND $tableAlias.store_id = 0",
                []
            );
            
            $select->order("$tableAlias.value " . strtoupper($sortDirection));
        } else {
            // Default sort by entity_id
            $select->order("e.entity_id " . strtoupper($sortDirection));
        }
        
        // Ensure unique results
        $select->distinct();
        
        return $select;
    }
    
    /**
     * Get attribute ID by code
     */
    private function getAttributeId($attributeCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('eav_attribute'),
                ['attribute_id']
            )
            ->where('attribute_code = ?', $attributeCode)
            ->where('entity_type_id = ?', 4); // 4 is catalog_product entity type
        
        return $connection->fetchOne($select);
    }
    
    /**
     * Get attribute type (varchar, decimal, int, etc.)
     */
    private function getAttributeType($attributeCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('eav_attribute'),
                ['backend_type']
            )
            ->where('attribute_code = ?', $attributeCode)
            ->where('entity_type_id = ?', 4); // 4 is catalog_product entity type
        
        return $connection->fetchOne($select);
    }

    /**
     * Get default minimum price
     */
    private function getDefaultPriceMin()
    {
        return 0;
    }

    /**
     * Get default maximum price
     */
    private function getDefaultPriceMax()
    {
        return 5000;
    }
}