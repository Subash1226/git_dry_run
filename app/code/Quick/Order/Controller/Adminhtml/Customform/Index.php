<?php
declare(strict_types=1);

namespace Quick\Order\Controller\Adminhtml\Customform;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Quick_Order::menu');
		$resultPage->addBreadcrumb(__('Prescription'), __('Prescription'));
        $resultPage->addBreadcrumb(__('Manage Prescription'), __('Manage Prescription'));
            $resultPage->getConfig()->getTitle()->prepend(__("Manage Prescription"));
            return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Quick_Order::manage');
    }
}

