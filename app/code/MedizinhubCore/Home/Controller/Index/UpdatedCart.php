<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class UpdatedCart extends Action
{
    protected $jsonFactory;
    protected $cart;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
    }

    public function execute()
    {
        $response = ['success' => false];

        $productId = (int) $this->getRequest()->getParam('product_id'); // Corrected parameter name
        $qty = (int) $this->getRequest()->getParam('qty');

        // Validate product ID and quantity
        if ($productId && $qty > 0) { // Adjusted validation condition for quantity
            try {
                // Get cart
                $cart = $this->cart->getQuote();

                // Find item in cart by product ID
                foreach ($cart->getAllVisibleItems() as $item) {
                    if ($item->getProductId() == $productId) {
                        $item->setQty($qty)->save(); // Set new quantity and save the item
                        $response['success'] = true;
                        $response['message'] = __('Cart updated successfully');
                        break; // Exit loop once item is found and updated
                    }
                }

                // Save cart
                $cart->collectTotals()->save();
            } catch (\Exception $e) {
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['message'] = __('Invalid product ID or quantity');
        }

        // Return JSON response
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
