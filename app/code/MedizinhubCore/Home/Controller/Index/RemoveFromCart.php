<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class RemoveFromCart extends Action
{
    protected $jsonFactory;
    protected $cart;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
        parent::__construct($context);
    }

    public function execute()
    {
        $response = ['success' => false];

        $productId = (int) $this->getRequest()->getParam('product_id'); // Correct parameter name

        // Validate product ID
        if ($productId) {
            try {
                // Get cart
                $cart = $this->cart->getQuote();

                // Find item in cart by product ID
                foreach ($cart->getAllVisibleItems() as $item) {
                    if ($item->getProductId() == $productId) {
                        $cart->removeItem($item->getId())->save(); // Remove item from cart and save changes
                        $response['success'] = true;
                        $response['message'] = __('Product removed from cart successfully');
                        break; // Exit loop once item is found and removed
                    }
                }

                // Save cart
                $cart->collectTotals()->save();
            } catch (\Exception $e) {
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['message'] = __('Invalid product ID');
        }

        // Return JSON response
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
