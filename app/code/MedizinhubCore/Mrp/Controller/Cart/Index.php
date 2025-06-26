<?php
namespace MedizinhubCore\Mrp\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends \Magento\Checkout\Controller\Cart\Index implements HttpGetActionInterface
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Cart'));
        return $resultPage;
    }
}
