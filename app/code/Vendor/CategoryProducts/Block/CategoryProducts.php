<?php
namespace Vendor\CategoryProducts\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;

class CategoryProducts extends Template
{
    protected $categoryFactory;
    protected $productCollectionFactory;
    protected $storeManager;
    protected $registry;

    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCategory()
    {
        $categoryId = $this->getRequest()->getParam('id');
        $category = $this->categoryFactory->create()->load($categoryId);

        if ($category->getId()) {
            $this->registry->register('current_category', $category);
            return $category;
        }
        return false;
    }

    public function getProductCollectionByCategories($categoryId)
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = 24;

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => $categoryId])
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setPageSize($pageSize)
            ->setCurPage($page);

        return $collection;
    }

    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
