<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;

class AjaxController extends Action
{
    protected $jsonResultFactory;
    protected $cart;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cart = $cart;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $items = [];

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
            $items[] = [
                'product_id' => $item->getProduct()->getId(),
                'name' => $item->getName(),
                'qty' => $item->getQty(),
                'price' => $item->getPrice(),
            ];
        }

        $result->setData(['items' => $items]);
        return $result;
    }
}


?>
