<?php

namespace CustomInvoice\Payment\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use CustomInvoice\Payment\Model\PaymentFactory;

/**
 * Class NewAction
 * @package CustomInvoice\Payment\Controller\Adminhtml\Payment
 */
class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param PageFactory $rawFactory
     * @param PaymentFactory $_paymentFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $rawFactory,
        PaymentFactory $_paymentFactory,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->pageFactory = $rawFactory;
        $this->_paymentFactory = $_paymentFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }


    /**
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('CustomInvoice_Payment::home');
        $title = __('Make New Payment');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }   
}