<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class AddToCart extends \Magento\Framework\App\Action\Action
{
    protected $cart;
    protected $jsonResultFactory;

    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $jsonResultFactory
    ) {
        $this->cart = $cart;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');
        $qty = (int) $this->getRequest()->getParam('qty', 1);

        $result = ['success' => false];

        if ($productId) {
            try {
                $this->cart->addProduct($productId, ['qty' => $qty]);
                $this->cart->save();
                $result['success'] = true;
                $result['message'] = __('Product added to cart successfully.');
            } catch (\Exception $e) {
                $result['message'] = __('Error: %1', $e->getMessage());
            }
        } else {
            $result['message'] = __('Invalid product ID.');
        }

        $resultJson = $this->jsonResultFactory->create();
        return $resultJson->setData($result);
    }
}
