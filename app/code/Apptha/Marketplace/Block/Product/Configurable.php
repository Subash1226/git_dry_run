<?php

namespace Apptha\Marketplace\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is used to manipulate the configurable product section.
 */
class Configurable extends Template
{
    protected $categoryCollectionFactory;
    protected $productFactory;
    protected $configurableProductType;
    protected $productAttributeRepository;
    protected $stockRegistry;
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductFactory $productFactory
     * @param ConfigurableProductType $configurableProductType
     * @param ProductAttributeRepository $productAttributeRepository
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductFactory $productFactory,
        ConfigurableProductType $configurableProductType,
        ProductAttributeRepository $productAttributeRepository,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productFactory = $productFactory;
        $this->configurableProductType = $configurableProductType;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get product types from the configuration.
     *
     * @return array
     */
    public function getProductTypes() {
        $productTypes = $this->_scopeConfig->getValue('marketplace/product_types'); // Replace with the correct configuration path.
        return $productTypes !== null ? explode(',', $productTypes) : [];
    }

    /**
     * Get configurable attributes ajax URL.
     *
     * @return string
     */
    public function getConfigurableAttributesUrl() {
        return $this->getUrl('marketplace/configurable/attributes');
    }

    /**
     * Get configurable attribute options ajax URL.
     *
     * @return string
     */
    public function getConfigurableOptionsUrl() {
        return $this->getUrl('marketplace/configurable/options');
    }

    /**
     * Get configurable bulk images & price ajax URL.
     *
     * @return string
     */
    public function getConfigurableBulkUrl() {
        return $this->getUrl('marketplace/configurable/image');
    }

    /**
     * Get attribute set ID.
     *
     * @return int
     */
    public function getAttributeSetId() {
        return $this->productFactory->create()->getDefaultAttributeSetId();
    }

    /**
     * Get configurable product data.
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getConfigurableProductData($productId) {
        return $this->productFactory->create()->load($productId);
    }

    /**
     * Get configurable product attributes.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getConfigurableProductAttributes($product) {
        return $this->configurableProductType->getUsedProductAttributeIds($product);
    }

    /**
     * Get configurable product attribute label by attribute code.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getConfigurableProductAttributeLabel($attributeCode) {
        return $this->productAttributeRepository->get($attributeCode)->getFrontendLabel();
    }

    /**
     * Get used associated product data.
     *
     * @param \Magento\Catalog\Model\Product $productData
     * @return array
     */
    public function getUsedAssociatedProductData($productData) {
        $usedProducts = $this->configurableProductType->getUsedProductCollection($productData)->getData();
        $associatedProductIds = array_column($usedProducts, 'entity_id');
        return $associatedProductIds;
    }

    /**
     * Get quantity for configurable associated product.
     *
     * @param int $usedProductId
     * @return int
     */
    public function getQtyForConfigurableAssociatedProduct($usedProductId) {
        return $this->stockRegistry->getStockItem($usedProductId)->getQty();
    }

    /**
     * Get store categories.
     *
     * @param bool $includeEmpty
     * @return array
     */
    public function getStoreCategories($includeEmpty = true)
    {
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->setOrder('name', 'ASC');

        if (!$includeEmpty) {
            $categoryCollection->addFieldToFilter('parent_id', ['neq' => 0]);
        }

        return $categoryCollection->getItems();
    }

    /**
     * Get simple product media image URL.
     *
     * @return string
     */
    public function getSimpleProductMediaImageUrl() {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
    }

    /**
     * Get base currency code for configurable product variants.
     *
     * @return string
     */
    public function getAssociatedVariantsBaseCurrency() {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get associated product IDs.
     *
     * @param array $simpleProducts
     * @return array
     */
    public function getAssociatedProductIds($simpleProducts) {
        $simpleProductSkus = array_filter(
            array_map(
                function ($simpleProduct) {
                    $splitSimpleProductsAttributes = explode(",", $simpleProduct);
                    foreach ($splitSimpleProductsAttributes as $splitSimpleProductsAttribute) {
                        $splitSimpleProductsSku = explode("=", $splitSimpleProductsAttribute);
                        if ($splitSimpleProductsSku[0] == 'sku') {
                            return $splitSimpleProductsSku[1];
                        }
                    }
                    return null;
                },
                $simpleProducts
            )
        );

        if (count($simpleProductSkus) >= 1) {
            $productModel = $this->productFactory->create()->getCollection();
            $productModel->addFieldToFilter('sku', ['in' => $simpleProductSkus]);
            return $productModel->getColumnValues('entity_id');
        }

        return [];
    }

    /**
     * Add product custom attributes.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $customAttributes
     * @param array $productData
     * @return \Magento\Catalog\Model\Product
     */
    public function addCustomAttributes($product, $customAttributes, $productData) {
        $customAttributeArray = [];
        foreach ($customAttributes as $customAttribute) {
            if (isset($productData[$customAttribute])) {
                $customAttributeArray[$customAttribute] = is_array($productData[$customAttribute])
                    ? implode(',', $productData[$customAttribute])
                    : $productData[$customAttribute];
            }
        }
        if (!empty($customAttributeArray)) {
            $product->addData($customAttributeArray);
        }
        return $product;
    }

    /**
     * Get edit existing product URL.
     *
     * @param int $productId
     * @return string
     */
    public function getProductEditUrl($productId) {
        return $this->getUrl('marketplace/product/add', ['config' => '1', 'product_id' => $productId]);
    }
}
