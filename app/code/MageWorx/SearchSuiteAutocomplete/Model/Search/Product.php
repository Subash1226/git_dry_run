<?php

namespace MageWorx\SearchSuiteAutocomplete\Model\Search;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Review\Model\ResourceModel\Review\SummaryFactory;
use Magento\Review\Model\Review;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SearchSuiteAutocomplete\Block\Autocomplete\ProductAgregator;
use MageWorx\SearchSuiteAutocomplete\Helper\Data as HelperData;
use MageWorx\SearchSuiteAutocomplete\Model\SearchInterface;
use MageWorx\SearchSuiteAutocomplete\Model\Source\AutocompleteFields;
use MageWorx\SearchSuiteAutocomplete\Model\Source\ProductFields;

class Product implements SearchInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SummaryFactory
     */
    protected $sumResourceFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var SearchHelper
     */
    protected $searchHelper;

    /**
     * @var LayerResolver
     */
    protected $layerResolver;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Product constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SummaryFactory $sumResourceFactory
     * @param HelperData $helperData
     * @param SearchHelper $searchHelper
     * @param LayerResolver $layerResolver
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SummaryFactory $sumResourceFactory,
        HelperData $helperData,
        SearchHelper $searchHelper,
        LayerResolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->storeManager = $storeManager;
        $this->sumResourceFactory = $sumResourceFactory;
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function getResponseData(): array
    {
        $responseData = [
            'code' => AutocompleteFields::PRODUCT,
            'data' => [],
        ];

        if (!$this->canAddToResult()) {
            return $responseData;
        }

        $query = $this->queryFactory->get();
        $queryText = $query->getQueryText();
        $productResultFields = array_merge(
            $this->helperData->getProductResultFieldsAsArray(),
            [ProductFields::URL, 'quantity_contents', 'special_price', 'old_price']
        );

        $productCollection = $this->getProductCollection($queryText);

        foreach ($productCollection as $product) {
            $responseData['data'][] = array_intersect_key(
                $this->getProductData($product),
                array_flip($productResultFields)
            );
        }

        $responseData['size'] = $productCollection->getSize();
        $responseData['url'] = $productCollection->getSize() > 0 ? $this->searchHelper->getResultUrl($queryText) : '';

        $query->saveNumResults($responseData['size']);
        $query->saveIncrementalPopularity();

        return $responseData;
    }

    /**
     * Get the label for quantity contents attribute
     *
     * @param ProductModel $product
     * @return string
     */
    protected function getQuantityContentsLabel(ProductModel $product): string
    {
        $selectedQuantityContent = $product->getData('quantity_contents');
        $quantityContentLabel = '';

        try {
            $attribute = $this->attributeRepository->get(
                ProductModel::ENTITY,
                'quantity_contents'
            );
            $options = $attribute->getSource()->getAllOptions(false);

            foreach ($options as $option) {
                if ($option['value'] == $selectedQuantityContent) {
                    $quantityContentLabel = $option['label'];
                    break;
                }
            }
        } catch (\Exception $e) {
            // Log the exception if needed
            return (string) $selectedQuantityContent;
        }

        return (string) $quantityContentLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddToResult(): bool
    {
        return in_array(AutocompleteFields::PRODUCT, $this->helperData->getAutocompleteFieldsAsArray(), true);
    }

    /**
     * Retrieve product collection by query text
     *
     * @param string $queryText
     * @return ProductCollection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getProductCollection(string $queryText): ProductCollection
    {
        $productResultNumber = $this->helperData->getProductResultNumber();
        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);

        $productCollection = $this->layerResolver->get()
            ->getProductCollection()
            ->addAttributeToSelect([
                ProductFields::DESCRIPTION,
                ProductFields::SHORT_DESCRIPTION,
                'quantity_contents',
                'special_price',
                'price'
            ])
            ->setPageSize($productResultNumber)
            ->addAttributeToSort('relevance')
            ->setOrder('relevance');

        // Fixes a bug when re-adding a Search Filter
        if ($this->queryFactory->get()->isQueryTextShort()) {
            $productCollection->addSearchFilter($queryText);
        }

        $sumResource = $this->sumResourceFactory->create();
        $sumResource->appendSummaryFieldsToCollection(
            $productCollection,
            $this->getStoreId(),
            Review::ENTITY_PRODUCT_CODE
        );

        return $productCollection;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Retrieve all product data
     *
     * @param ProductModel $product
     * @return array
     * @throws LocalizedException
     */
    protected function getProductData(ProductModel $product): array
    {
        /** @var ProductAgregator $productAgregator */
        $productAgregator = $this->objectManager->create(ProductAgregator::class)->setProduct($product);

        $data = [
            ProductFields::NAME => $productAgregator->getName(),
            ProductFields::SKU => $productAgregator->getSku(),
            ProductFields::IMAGE => $productAgregator->getSmallImage(),
            ProductFields::REVIEWS_RATING => $productAgregator->getReviewsRating(),
            ProductFields::SHORT_DESCRIPTION => $productAgregator->getShortDescription(),
            ProductFields::DESCRIPTION => $productAgregator->getDescription(),
            ProductFields::PRICE => $productAgregator->getPrice(),
            ProductFields::URL => $productAgregator->getUrl(),
            'quantity_contents' => $this->getQuantityContentsLabel($product),
            'special_price' => $this->getSpecialPrice($product),
            'old_price' => $this->getOldPrice($product),
        ];

        if ($product->getData('is_salable')) {
            $data[ProductFields::ADD_TO_CART] = $productAgregator->getAddToCartData();
        }

        return $data;
    }

    /**
     * Get special price for the product
     *
     * @param ProductModel $product
     * @return float|null
     */
    protected function getSpecialPrice(ProductModel $product): ?float
    {
        $specialPrice = $product->getSpecialPrice();
        return $specialPrice !== null && $specialPrice !== false ? (float) $specialPrice : null;
    }

    /**
     * Get old price (regular price) for the product
     *
     * @param ProductModel $product
     * @return float|null
     */
    protected function getOldPrice(ProductModel $product): ?float
    {
        $oldPrice = $product->getPrice();
        return $oldPrice !== null && $oldPrice !== false ? (float) $oldPrice : null;
    }
}
