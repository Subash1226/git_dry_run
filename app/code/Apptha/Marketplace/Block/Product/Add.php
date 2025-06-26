<?php
/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Marketplace
 * @version     1.2
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2017 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
namespace Apptha\Marketplace\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Resource\Product\CollectionFactory;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Apptha\Marketplace\Helper\Data as MarketplaceHelper;
use Apptha\Marketplace\Helper\System as SystemHelper;



/**
 * This class used to display product add/edit form
 */
class Add extends \Magento\Framework\View\Element\Template {
    protected $storeManager;
    protected $attributeSet;
    protected $product;
    protected $categoryFlatConfig;
    protected $categoryRepository;
    protected $scopeConfig;
    protected $categoryHelper;
    protected $marketplaceHelper;
    protected $systemHelper;

    public function __construct(
        Context $context,
        AttributeSetCollection $attributeSet,
        \Magento\Catalog\Model\Product $product,
        State $categoryFlatState,
        CategoryRepositoryInterface $categoryRepository,
        ScopeConfigInterface $scopeConfig,
        CategoryHelper $categoryHelper,
        MarketplaceHelper $marketplaceHelper,
        SystemHelper $systemHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $context->getStoreManager();
        $this->attributeSet = $attributeSet;
        $this->product = $product;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->categoryHelper = $categoryHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->systemHelper = $systemHelper;
    }

    /**
     * Prepare layout for add product
     *
     * @return object
     */
    public function _prepareLayout() {
        $productId = $this->getRequest ()->getParam ( 'product_id' );
        if (! empty ( $productId )) {
            $this->pageConfig->getTitle ()->set ( __ ( 'Edit Product' ) );
        } else {
            $this->pageConfig->getTitle ()->set ( __ ( 'Add Product' ) );
        }
        return parent::_prepareLayout ();
    }

    /**
     * Get base currency symbol
     *
     * @return string
     */
    public function getBaseCurrency() {
        return $this->storeManager->getStore ()->getBaseCurrencyCode ();
    }

    /**
     * Get save product action url
     *
     * @return string
     */
    public function getPostActionUrl() {
        return $this->getUrl ( 'marketplace/product/savedata' );
    }

    /**
     * Get Default Attribute Set Id
     *
     * @return int
     */
    public function getDefaultAttributeSetId() {
        return $this->product->getDefaultAttributeSetId ();
    }

    /**
     * Get Attribute set datas
     *
     * @return array
     */
    public function getAttributeSet() {
        return $this->attributeSet->toOptionArray ();
    }

    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories('name', $asCollection, $toLoad);
    }

    public function alphabaticalOrder($categories, $catChecked)
    {
        $categoryName = [];
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $categoryId = $category->getId();
            if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
                (array)$category->getChildrenNodes();
            } else {
                $category->getChildren();
            }
            if ($category->hasChildren()) {
                $categoryId .= 'sub';
            }
            $categoryName[$categoryId] = $category->getName();
        }
        asort($categoryName);
        return $this->marketplaceHelper->showCategoriesTree($categoryName, $catChecked);
    }


    /**
     * Get ajax category tree action url
     *
     * @return string
     */
    public function getCategoryTreeAjaxUrl() {
        return $this->getUrl ( 'marketplace/product/category' );
    }

    /**
     * Get ajax image upload action url
     *
     * @return string
     */
    public function getImageUploadAjaxUrl() {
        return $this->getUrl ( 'marketplace/product/imageupload' );
    }

    /**
     * Getting product data
     *
     * @param int $productId
     *
     * @return object $productData
     */
    public function getProductData($productId) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Magento\Catalog\Model\Product' )->load ( $productId );
    }

    /**
     * Getting stock state object
     *
     * @param int $productId
     *
     * @return object $stockData
     */
    public function getProductStockDataQty($productId) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Magento\CatalogInventory\Api\Data\StockItemInterface' )->load ( $productId, 'product_id' );
    }

    /**
     * Get media image url
     *
     * @return string $mediaImageUrl
     */
    public function getMediaImageUrl() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Magento\Store\Model\StoreManagerInterface' )->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'catalog/product';
    }
    /**
     * Get product approval or not
     *
     * @return int
     */
    public function getProductApproval() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Apptha\Marketplace\Helper\System' )->getProductApproval ();
    }

    /**
     * Get custom attributes
     *
     * @return int
     */
    public function geCustomAttributes() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Apptha\Marketplace\Helper\System' )->geCustomAttributes ();
    }

    /**
     * Get product types
     *
     * @return int
     */
    public function getProductTypes() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Apptha\Marketplace\Helper\System' )->getProductTypes ();
    }

    /**
     * Get product custom options enabled or not
     *
     * @return int
     */
    public function getProductCustomOptions() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager->get ( 'Apptha\Marketplace\Helper\System' )->getProductCustomOptions ();
    }

    /**
     * Get ajax url for sku validation
     *
     * @return string
     */
    public function getSkuValidateAjaxUrl() {
        return $this->getUrl ( 'marketplace/product/skuvalidate' );
    }
    public function renderCategoryTree($categories)
    {
        $html = '<ul>';
        foreach ($categories as $category) {
            $html .= '<li>' . $category->getName();
            if ($category->hasChildren()) {
                $html .= $this->renderCategoryTree($category->getChildrenCategories());
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Checking whether seller store shipping enabled or not
     *
     * @return boolean
     */
    public function isSellerProductShipping() {
        $isSellerProductShipping = 0;
        $objectModelManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $isSellerShippingType = $objectModelManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' )->getValue ( 'carriers/apptha/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        $isSellerShippingEnabled = $objectModelManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' )->getValue ( 'carriers/apptha/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        if ($isSellerShippingEnabled == 1 && $isSellerShippingType == 'product') {
            $isSellerProductShipping = 1;
        }
        return $isSellerProductShipping;
    }

    /**
     * Get custom attributes ajax url
     *
     * @return string
     */
    public function getCustomAttributesUrl() {
        return $this->getUrl ( 'marketplace/product/attributes' );
    }
}
