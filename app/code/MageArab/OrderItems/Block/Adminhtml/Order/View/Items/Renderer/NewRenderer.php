<?php

namespace MageArab\OrderItems\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Registry;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use MageArab\OrderItems\Helper\Data as OrderItemsHelper;
use Psr\Log\LoggerInterface;

class NewRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
    protected $_messageHelper;
    protected $_checkoutHelper;
    protected $_giftMessage = [];
    protected $_imageHelper;
    protected $_dataHelper;
    protected $_productRepository;
    protected $_logger;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param GiftMessageHelper $messageHelper
     * @param CheckoutHelper $checkoutHelper
     * @param Image $imageHelper
     * @param OrderItemsHelper $dataHelper
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        GiftMessageHelper $messageHelper,
        CheckoutHelper $checkoutHelper,
        Image $imageHelper,
        OrderItemsHelper $dataHelper,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_messageHelper = $messageHelper;
        $this->_imageHelper = $imageHelper;
        $this->_dataHelper = $dataHelper;
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper, $checkoutHelper, $data);
    }

    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $product = $item->getProduct();
        $imageUrl = $this->_imageHelper->init($product, 'small_image')->setImageFile($product->getSmallImage())->resize(50, 50)->getUrl();
        $html = '';

        switch ($column) {
            case 'status':
                $html = $item->getStatus();
                break;
            case 'shipping_status':
                $html = $this->_dataHelper->getShippingStatusLabel($item->getShippingStatus());
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('original_price');
                break;
            case 'tax-amount':
                $html = $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-percent':
                $html = $this->displayTaxPercent($item);
                break;
            case 'discount':
                $html = $this->displayPriceAttribute('discount_amount');
                break;
            case 'rack':
                $html .= '<div class="editable-column" id="' . $this->getHtmlId() . '">';
                $html .= $this->getRack($product);
                $html .= '</div>';
                break;
            case 'barcode':
                $html = $item->getBarcode();
                break;
            case 'batch':
                $html .= '<div class="editable-column" id="' . $this->getHtmlId() . '">';
                $html .= $this->getBatchId($product);
                $html .= '</div>';
                break;
                case 'expiry':
                    $html .= '<div class="editable-column" id="' . $this->getHtmlId() . '">';
                    $html .= $this->getExpiryDate($product);
                    $html .= '</div>';
                    break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    /**
     * Load product by ID
     *
     * @param int $productId
     * @return ProductInterface|null
     */
    protected function loadProduct($productId)
    {
        try {
            return $this->_productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_logger->error('Product not found: ' . $productId);
            return null;
        }
    }

   /**
 * Custom method to get Batch ID
 *
 * @param ProductInterface $product
 * @return string
 */
protected function getBatchId(ProductInterface $product)
{
    if (!$product) {
        return 'N/A';
    }

    $productId = $product->getId();
    $batchId = 'N/A';

    try {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('snowdog_custom_description');
        $select = $connection->select()
            ->from($tableName, ['title'])
            ->where('product_id = ?', $productId)
            ->limit(1);
        $batchId = $connection->fetchOne($select);

        if (!$batchId) {
            $this->_logger->debug('Batch ID not found in custom table for product ID: ' . $productId);
            $batchId = 'N/A';
        }
    } catch (\Exception $e) {
        $this->_logger->error('Error fetching Batch ID for product ID: ' . $productId . '. Error: ' . $e->getMessage());
    }

    return $batchId;
}
/**
 * @param ProductInterface $product
 * @return string
 */
protected function getExpiryDate(ProductInterface $product)
{
    if (!$product) {
        return 'N/A';
    }

    $productId = $product->getId();
    $expiryDate = 'N/A';

    try {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('snowdog_custom_description');

        // Fetch Expiry Date
        $select = $connection->select()
            ->from($tableName, ['expiry_date'])
            ->where('product_id = ?', $productId)
            ->limit(1);
        $expiryDate = $connection->fetchOne($select);

        if (!$expiryDate) {
            $this->_logger->debug('Expiry date not found in custom table for product ID: ' . $productId);
            $expiryDate = 'N/A';
        }

    } catch (\Exception $e) {
        $this->_logger->error('Error fetching expiry date for product ID: ' . $productId . '. Error: ' . $e->getMessage());
    }

    return $expiryDate;
}


    /**
     * Custom method to get Rack
     *
     * @param ProductInterface $product
     * @return string
     */
    protected function getRack(ProductInterface $product)
    {
        if (!$product) {
            return 'N/A';
        }
        $rackAttribute = $product->getCustomAttribute('rack_no');
        if (!$rackAttribute) {
            $this->_logger->debug('Rack attribute is missing for product ID: ' . $product->getId());
            return 'N/A';
        }
        return $rackAttribute->getValue();
    }
}
