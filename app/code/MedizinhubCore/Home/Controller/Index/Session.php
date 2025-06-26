<?php
namespace MedizinhubCore\Home\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;

class Session extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $consultingFee = (bool) $this->getRequest()->getPost('consulting_fee');

        // Set consulting fee session variable
        $this->customerSession->setConsultingFee($consultingFee);

        // Return JSON response
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
