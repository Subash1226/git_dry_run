<?php
namespace MedizinhubCore\Lab\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

class View implements HttpGetActionInterface
{
    protected $pageFactory;
    protected $request;

    public function __construct(
        PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }

    public function execute(): ResultInterface
    {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('Lab Test Details');
        return $page;
    }
}
