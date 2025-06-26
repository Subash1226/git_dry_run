<?php
namespace Mhb\SearchResult\Controller\Filter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CategorySearch extends Action
{
    protected $categoryCollectionFactory;
    protected $resourceConnection;
    protected $logger;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $search = $this->getRequest()->getParam('search');
        
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_category_product');

        // Get categories that have products
        $query = $connection->select()
            ->from($tableName, ['category_id'])
            ->group('category_id');

        $categoryIds = $connection->fetchCol($query);
        
        // Create category collection with filtering
        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'url_key', 'url_path'])
            ->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addFieldToFilter('is_active', 1);
            
        // Apply search filter if provided
        if (!empty($search)) {
            $categories->addAttributeToFilter('name', ['like' => '%' . $search . '%']);
        }
        
        $categories->setOrder('name', 'ASC');
            
        $html = '';
        
        try {
            foreach ($categories as $category) {
                $productCount = $this->getProductCountByCategory($category->getId());
                
                $html .= '<li class="cusrp-filter-item">';
                $html .= '<div class="cusrp-form-check">';
                $html .= '<input type="checkbox" class="cusrp-filter-checkbox" data-filter="category" data-value="' . $category->getId() . '" id="cat_' . $category->getId() . '">';
                $html .= '<label class="cusrp-filter-label" for="cat_' . $category->getId() . '">' . $category->getName() . ' (' . $productCount . ')</label>';
                $html .= '</div>';
                $html .= '</li>';
            }
            
            if (empty($html)) {
                $html = '<li class="cusrp-filter-item-empty">No categories found</li>';
            }
            
            // $this->logger->info('Category search completed for: ' . $search . ', found: ' . $categories->getSize() . ' results');
        } catch (\Exception $e) {
            $this->logger->error('Error in category search: ' . $e->getMessage());
            $html = '<li class="cusrp-filter-item-empty">Error loading categories</li>';
        }
        
        $this->getResponse()->setBody($html);
    }
    
    /**
     * Get product count for a category
     * @param int $categoryId
     * @return int
     */
    protected function getProductCountByCategory($categoryId)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('catalog_category_product');
            
            $select = $connection->select()
                ->from($tableName, [new \Zend_Db_Expr('COUNT(*)')])
                ->where('category_id = ?', $categoryId);
                
            return (int)$connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logger->error('Error getting product count for category ' . $categoryId . ': ' . $e->getMessage());
            return 0;
        }
    }
}