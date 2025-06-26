<?php
namespace Mhb\SearchResult\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Checkout\Helper\Cart as CartHelper;

class Filter extends Template
{
    protected $categoryCollectionFactory;
    protected $layerResolver;
    protected $productCollectionFactory;
    protected $eavConfig;
    protected $logger;
    protected $resourceConnection;
    protected $baseCollection;
    protected $pricingHelper;
    protected $imageHelper;
    protected $urlHelper;
    protected $cartHelper;
    protected $_productCollection;

    public function __construct(
        Template\Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        LayerResolver $layerResolver,
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        PricingHelper $pricingHelper,
        ImageHelper $imageHelper,
        UrlHelper $urlHelper,
        CartHelper $cartHelper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->layerResolver = $layerResolver;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->pricingHelper = $pricingHelper;
        $this->imageHelper = $imageHelper;
        $this->urlHelper = $urlHelper;
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    public function setProductCollection($collection)
    {
        // $this->logger->info('Setting product collection with size: ' . $collection->getSize());
        $this->_productCollection = $collection;
        return $this;
    }

    /**
     * Get product collection for display with pagination
     */
    public function getProductCollection()
    {
        if ($this->_productCollection !== null) {
            // $this->logger->info('Returning stored product collection with size: ' . $this->_productCollection->getSize());
            return $this->_productCollection;
        }

        if (!$this->baseCollection) {
            try {
                $layer = $this->layerResolver->get();
                if ($layer && $layer->getCurrentCategory()) {
                    $collection = $layer->getProductCollection();
                    // $this->logger->info('Using category layer collection');
                } else {
                    $this->layerResolver->create('search');
                    $layer = $this->layerResolver->get();
                    if ($layer) {
                        $collection = $layer->getProductCollection();
                        // $this->logger->info('Using search layer collection');
                    } else {
                        $collection = $this->productCollectionFactory->create();
                        $collection->addAttributeToSelect('*');
                        // $this->logger->info('Using default product collection');
                    }
                }
                if (!$collection) {
                    $collection = $this->productCollectionFactory->create();
                    $collection->addAttributeToSelect('*');
                }
                
                // Apply pagination to the base collection
                $this->applyPagination($collection);
                
                $this->baseCollection = $collection;
                // $this->logger->info('Base collection initialized with ' . $collection->getSize() . ' products');
            } catch (\Exception $e) {
                // $this->logger->error('Error initializing product collection: ' . $e->getMessage());
                $this->baseCollection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
                $this->applyPagination($this->baseCollection);
            }
        }
        return clone $this->baseCollection;
    }

    /**
     * Apply pagination to collection
     */
    protected function applyPagination($collection)
    {
        $page = $this->getCurrentPage();
        $pageSize = $this->getPageSize();
        
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        
        return $collection;
    }

    /**
     * Get current page number
     */
    public function getCurrentPage()
    {
        return (int)$this->getRequest()->getParam('page', 1);
    }

    /**
     * Get page size
     */
    public function getPageSize()
    {
        return (int)$this->getRequest()->getParam('page_size', 12);
    }

    /**
     * Get total number of products without pagination
     */
    public function getTotalProductCount()
    {
        try {
            $countCollection = clone $this->getBaseCollectionForCount();            
            return $countCollection->getSize();
        } catch (\Exception $e) {
            // $this->logger->error('Error getting total product count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get base collection for counting (without pagination)
     */
    protected function getBaseCollectionForCount()
    {
        try {
            $layer = $this->layerResolver->get();
            if ($layer && $layer->getCurrentCategory()) {
                $collection = $layer->getProductCollection();
            } else {
                $this->layerResolver->create('search');
                $layer = $this->layerResolver->get();
                if ($layer) {
                    $collection = $layer->getProductCollection();
                } else {
                    $collection = $this->productCollectionFactory->create();
                    $collection->addAttributeToSelect('*');
                }
            }
            if (!$collection) {
                $collection = $this->productCollectionFactory->create();
                $collection->addAttributeToSelect('*');
            }
            
            return $collection;
        } catch (\Exception $e) {
            return $this->productCollectionFactory->create()->addAttributeToSelect('*');
        }
    }

    /**
     * Get total pages
     */
    public function getTotalPages()
    {
        $totalCount = $this->getTotalProductCount();
        $pageSize = $this->getPageSize();
        
        return $pageSize > 0 ? ceil($totalCount / $pageSize) : 1;
    }

    /**
     * Check if there are multiple pages
     */
    public function hasMultiplePages()
    {
        if ($this->hasData('totalPages')) {
            return $this->getData('totalPages') > 1;
        }
        
        return $this->getTotalPages() > 1;
    }

    /**
     * Get pagination info for template
     */
    public function getPaginationInfo()
    {
        // If pagination data is set by controller (from filter), use it
        if ($this->hasData('page') && $this->hasData('totalPages')) {
            return [
                'current_page' => $this->getData('page'),
                'total_pages' => $this->getData('totalPages'),
                'page_size' => $this->getData('page_size') ?: $this->getPageSize()
            ];
        }
        
        // Default calculation for non-filtered pages
        return [
            'current_page' => $this->getCurrentPage(),
            'total_pages' => $this->getTotalPages(),
            'page_size' => $this->getPageSize()
        ];
    }

    /**
     * Format price
     */
    public function formatPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    public function getImageUrl($product, $imageId)
    {
        try {
            return $this->imageHelper->init($product, $imageId)->getUrl();
        } catch (\Exception $e) {
            // $this->logger->error('Error getting product image URL: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Fetch up to 10 distinct categories that have products
     */
    public function getCategories()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_category_product');

        $query = $connection->select()
            ->from($tableName, ['category_id'])
            ->group('category_id');

        $categoryIds = $connection->fetchCol($query);

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addFieldToFilter('is_active', 1)
            ->setOrder('name', 'ASC');

        // Add product count to each category
        foreach ($categories as $category) {
            $productCount = $this->getProductCountByCategory($category->getId());
            $category->setData('product_count', $productCount);
        }

        // $this->logger->info('Loaded ' . $categories->getSize() . ' active categories for filtering');
        return $categories;
    }

    /**
     * Fetch manufacturers with optional search
     */
    public function getManufacturers($limit = 10, $search = '')
    {
        try {
            // Use EAV to get manufacturer attribute ID
            $manufacturerAttribute = $this->eavConfig->getAttribute('catalog_product', 'manufacturer');
            if (!$manufacturerAttribute) {
                // $this->logger->error('Manufacturer attribute not found');
                return [];
            }
            
            $manufacturerAttributeId = $manufacturerAttribute->getAttributeId();
            
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('catalog_product_entity_varchar');

            $query = $connection->select()
                ->from($tableName, ['value'])
                ->where('attribute_id = ?', $manufacturerAttributeId)
                ->where('value IS NOT NULL')
                ->where('value != ""')
                ->group('value')
                ->order('value ASC');

            if ($search) {
                $query->where('value LIKE ?', '%' . $search . '%');
            }
            
            $query->limit($limit);
            
            $manufacturers = $connection->fetchCol($query);

            $options = [];
            foreach ($manufacturers as $manufacturer) {
                if (!empty(trim($manufacturer))) {
                    $options[] = ['value' => $manufacturer, 'label' => $manufacturer];
                }
            }
            
            // $this->logger->info('Loaded ' . count($options) . ' manufacturers for filtering');
            return array_slice($options, 0, $limit);
        } catch (\Exception $e) {
            // $this->logger->error('Error getting manufacturers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hardcoded prescription options
     */
    public function getPrescriptionOptions()
    {
        return [
            ['value' => '37', 'label' => 'Yes'],
            ['value' => '38', 'label' => 'No']
        ];
    }

    /**
     * Get min and max price range from all products
     */
    public function getPriceRange()
    {
        try {
            // Get price attribute ID
            $connection = $this->resourceConnection->getConnection();
            $attrTable = $this->resourceConnection->getTableName('eav_attribute');
            
            $select = $connection->select()
                ->from($attrTable, ['attribute_id'])
                ->where('attribute_code = ?', 'price')
                ->where('entity_type_id = ?', 4); // 4 is for product entity type
                
            $priceAttributeId = $connection->fetchOne($select);
            
            if (!$priceAttributeId) {
                return ['min' => 0, 'max' => 10000];
            }
            
            // Query min and max prices directly
            $priceTable = $this->resourceConnection->getTableName('catalog_product_entity_decimal');
            
            $select = $connection->select()
                ->from(
                    $priceTable, 
                    [
                        'min_price' => 'MIN(value)',
                        'max_price' => 'MAX(value)'
                    ]
                )
                ->where('attribute_id = ?', $priceAttributeId)
                ->where('value > 0');
                
            $result = $connection->fetchRow($select);
            
            $minPrice = isset($result['min_price']) ? floor($result['min_price']) : 0;
            $maxPrice = isset($result['max_price']) ? ceil($result['max_price']) : 10000;
            
            // $this->logger->info("Price range (direct query): {$minPrice} - {$maxPrice}");
            
            return [
                'min' => $minPrice,
                'max' => $maxPrice
            ];
        } catch (\Exception $e) {
            // $this->logger->error('Error getting price range: ' . $e->getMessage());
            return [
                'min' => 0,
                'max' => 10000 // Default range if there's an error
            ];
        }
    }

    /**
     * Get default minimum price
     */
    public function getDefaultPriceMin()
    {
        return 0;
    }

    /**
     * Get default maximum price
     */
    public function getDefaultPriceMax()
    {
        return 5000;
    }

    /**
     * Get a direct product count by category
     */
    public function getProductCountByCategory($categoryId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_category_product');
        
        $select = $connection->select()
            ->from($tableName, ['COUNT(*)'])
            ->where('category_id = ?', $categoryId);
            
        return $connection->fetchOne($select);
    }
}