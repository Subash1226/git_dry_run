<?php

namespace MedizinhubCore\ConfirmOrder\Block;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\App\ActionInterface;

/**
 * Customform content block
 */
class Confirm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Home constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getProductCollectionByCategories($ids)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $ids]);
        return $collection;
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        //$this->pageConfig->getTitle()->set(__(''));

        return parent::_prepareLayout();
    }
}

// namespace Vendor\Module\Block;
// use Magento\Catalog\Pricing\Price\FinalPrice;
// use Magento\Framework\Pricing\Render;
// use Magento\Framework\App\ActionInterface;

// class ProductList extends \Magento\Catalog\Block\Product\AbstractProduct
// {

//     /**
//      * @var \Magento\Framework\Url\Helper\Data
//      */
//     protected $urlHelper;

//     /**
//      * @var \Magento\Catalog\Model\ProductFactory
//      */
//     protected $productFactory;

//     /**
//      * @param Context $context
//      * @param \Magento\Framework\Url\Helper\Data $urlHelper
//      * @param \Magento\Catalog\Model\ProductFactory $productloader
//      * @param array $data
//      */
//     public function __construct(
//         \Magento\Catalog\Block\Product\Context $context,
//         \Magento\Framework\Url\Helper\Data $urlHelper,
//         \Magento\Catalog\Model\ProductFactory $productloader,
//         \Magento\Framework\Data\Form\FormKey $formKey,
//         array $data = []
//     ) {
//         $this->urlHelper = $urlHelper;
//         $this->productFactory = $productloader;
//         $this->formKey = $formKey;
//         parent::__construct($context, $data);
//     }
//     /**
//      * Get form key
//      *
//      * @return string
//      */
//     public function getFormKey()
//     {
//         return $this->formKey->getFormKey();
//     }
//     /**
//      * Load Products collection
//      *
//      * @return Product array
//      */
//     public function getLoadProducts()
//     {
//         $products = $this->productFactory->create()->getCollection()
//                     ->addAttributeToSelect(["name", "price", "image"])
//                     ->addAttributeToFilter("visibility", ['neq' => 1]);
//         return $products;
//     }
//     /**
//      * Load Product
//      *
//      * @return Product array
//      */
//     public function getLoadProduct($id)
//     {
//         return $this->productFactory->create()->load($id);
//     }

//     /**
//      * Get post parameters
//      *
//      * @param \Magento\Catalog\Model\Product $product
//      * @return array
//      */
//     public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
//     {
//         $url = $this->getAddToCartUrl($product, ['_escape' => false]);
//         return [
//             'action' => $url,
//             'data' => [
//                 'product' => (int) $product->getEntityId(),
//                 ActionInterface::PARAM_NAME_URL_ENCODED =>
//                     $this->urlHelper->getEncodedUrl($url),
//             ]
//         ];
//     }
//     /**
//      * Get product price.
//      *
//      * @param \Magento\Catalog\Model\Product $product
//      * @return string
//      */
//     public function getProductPrice(\Magento\Catalog\Model\Product $product)
//     {
//         $priceRender = $this->getPriceRender($product);
//         $price = '';
//         if ($priceRender) {
//             $price = $priceRender->render(
//                 FinalPrice::PRICE_CODE,
//                 $product,
//                 [
//                     'include_container' => true,
//                     'display_minimal_price' => true,
//                     'zone' => Render::ZONE_ITEM_LIST,
//                     'list_category_page' => true
//                 ]
//             );
//         }
//         return $price;
//     }

//     /**
//      * Get price render
//      *
//      * @param \Magento\Catalog\Model\Product $product
//      * @return Render
//      */
//     protected function getPriceRender($product)
//     {
//         return $this->getLayout()->createBlock(\Magento\Framework\Pricing\Render::class, "product.price.render.default".$product->getSku())
//             ->setData('is_product_list', true);
//     }
// }
