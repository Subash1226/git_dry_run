<?php

namespace MedizinhubCore\Home\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use MedizinhubCore\Lab\Block\Booking as BookingBlock;

class Home extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var BookingBlock
     */
    protected $bookingBlock;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var array
     */
    protected $productCollectionCache = [];

    /**
     * Home constructor.
     *
     * @param Context $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param UrlInterface $urlBuilder
     * @param ImageHelper $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param PricingHelper $pricingHelper
     * @param BookingBlock $bookingBlock
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        UrlInterface $urlBuilder,
        ImageHelper $imageHelper,
        StoreManagerInterface $storeManager,
        PricingHelper $pricingHelper,
        BookingBlock $bookingBlock,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->pricingHelper = $pricingHelper;
        $this->bookingBlock = $bookingBlock;
        parent::__construct($context, $data);
    }

    /**
     * Get cached product collection by ID
     * Implements caching to improve performance
     *
     * @param array $ids Category IDs
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCachedProductCollection($ids)
    {
        $key = implode('_', $ids);

        if (!isset($this->productCollectionCache[$key])) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*')
                      ->addCategoriesFilter(['in' => $ids])
                      ->addAttributeToFilter('visibility', ['neq' => 1])
                      ->addAttributeToFilter('status', 1)
                      ->setPageSize(10)
                      ->setCurPage(1)
                      ->addAttributeToSort('created_at', 'DESC');

            $this->productCollectionCache[$key] = $collection;
        }

        return $this->productCollectionCache[$key];
    }

    /**
     * Get base URL for the store
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get media base URL
     *
     * @return string
     */
    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get formatted product display data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductDisplayData($product)
    {
        $regularPrice = round($product->getPrice());
        $specialPrice = round($product->getSpecialPrice());

        if (!$specialPrice || $specialPrice >= $regularPrice) {
            $specialPrice = round($regularPrice);
        }

        $discountPercentage = 0;
        if ($regularPrice > 0 && $specialPrice < $regularPrice) {
            $discountPercentage = round(100 - ($specialPrice / $regularPrice * 100));
        }

        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'url' => $product->getProductUrl(),
            'imageUrl' => $this->imageHelper->init($product, 'product_page_image_small')
                                           ->setImageFile($product->getSmallImage())
                                           ->getUrl(),
            'regularPrice' => '₹ ' . $regularPrice,
            'specialPrice' => '₹ ' . $specialPrice,
            'discountPercentage' => $discountPercentage
        ];
    }

    /**
     * Get dynamic ID.
     *
     * @return int
     */
    public function getDynamicId()
    {
        // Implement logic to retrieve the dynamic ID dynamically
        // For example, you can get it from a request parameter
        $request = $this->getRequest();
        return $request->getParam('dynamic_id', 9);
    }

    /**
     * Get dynamic URL.
     *
     * @return string
     */
    public function getDynamicUrl()
    {
        $dynamicId = $this->getDynamicId();
        return $this->_urlBuilder->getUrl('product_list/index/index', ['id' => $dynamicId]);
    }

    /**
     * Generate the product list link based on the dynamic ID.
     *
     * @return string
     */
    public function generateProductListLink()
    {
        $dynamicId = $this->getDynamicId();
        return $this->_urlBuilder->getUrl('product_list/index/index', ['id' => $dynamicId]);
    }

    /**
     * Prepare layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // $this->pageConfig->getTitle()->set(__(''));
        return parent::_prepareLayout();
    }

    public function renderMaxDiscountLabel($category)
    {
        $maxDiscount = $this->bookingBlock->renderMaxDiscountLabel($category);
        return $maxDiscount;
    }
}
