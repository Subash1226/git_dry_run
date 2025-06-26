<?php

namespace RLTSquare\AjaxShoppingCartUpdate\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Catalog\Api\ProductRepositoryInterface;

class GetSalableQuantity extends Action
{
    private $resultJsonFactory;
    private $salableQuantityDataBySku;
    private $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GetSalableQuantityDataBySku $salableQuantityDataBySku,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->salableQuantityDataBySku = $salableQuantityDataBySku;
        $this->productRepository = $productRepository;
    }

    public function execute()
{
    $resultJson = $this->resultJsonFactory->create();
    $sku = $this->getRequest()->getParam('sku'); // Get SKU from request

    if (!$sku) {
        return $resultJson->setData([
            'success' => false,
            'message' => __('SKU is required.')
        ]);
    }

    try {
        $salableQuantityData = $this->salableQuantityDataBySku->execute($sku);

        $salableQuantity = 0;
        foreach ($salableQuantityData as $data) {
            $salableQuantity += $data['qty'];
        }

        return $resultJson->setData([
            'success' => true,
            'salable_quantity' => $salableQuantity
        ]);
    } catch (\Exception $e) {
        return $resultJson->setData([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

}
