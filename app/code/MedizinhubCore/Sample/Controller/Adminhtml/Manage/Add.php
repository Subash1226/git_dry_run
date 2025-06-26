<?php

namespace MedizinhubCore\Sample\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use MedizinhubCore\Sample\Model\ManageFactory;

/**
 * Class NewAction
 * @package MedizinhubCore\Sample\Controller\Adminhtml\Manage
 */
class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ManageFactory
     */
    protected $_manageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param PageFactory $rawFactory
     * @param ManageFactory $_manageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $rawFactory,
        ManageFactory $_manageFactory,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->pageFactory = $rawFactory;
        $this->_manageFactory = $_manageFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }


    /**
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('MedizinhubCore_Sample::home');
        $title = __('Add Entries');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Sample::add');
    }
}
