<?php

namespace MedizinhubCore\Patient\Controller\Adminhtml\Appointments;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use MedizinhubCore\Patient\Model\AppointmentsFactory;

/**
 * Class NewAction
 * @package MedizinhubCore\Patient\Controller\Adminhtml\Appointments
 */
class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var AppointmentsFactory
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
     * @param AppointmentsFactory $_manageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $rawFactory,
        AppointmentsFactory $_manageFactory,
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
        $resultPage->setActiveMenu('MedizinhubCore_Patient::main_menu');
        $title = __('Add Appointments');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
