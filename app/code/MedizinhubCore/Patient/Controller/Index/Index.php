<?php

namespace MedizinhubCore\Patient\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    protected $resultPageFactory;
    protected $customerSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $consultantType = $this->getRequest()->getParam('consultantType');

        if ($consultantType) {
            $this->customerSession->setConsultantType($consultantType);
        }

        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }
}
