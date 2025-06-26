<?php

namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;

class AddressList extends Action
{
    protected $resultJsonFactory;
    protected $layoutFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(\Quick\Order\Block\AddressList::class);
        $html = $block->toHtml();

        $result = $this->resultJsonFactory->create();
        return $result->setData(['html' => $html]);
    }
}
