<?php

namespace MedizinhubCore\Patient\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use MedizinhubCore\Patient\Model\PatientFactory;

class DeletePatient extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $patientFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $customerSession,
        PatientFactory $patientFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->patientFactory = $patientFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $patientId = $this->getRequest()->getParam('id');

            try {
                $patient = $this->patientFactory->create()->load($patientId);
                if($patient->getId() && $patient->getCustomerId() == $customerId) {
                    $patient->delete();
                    $result = ['success' => true, 'message' => 'Patient deleted successfully'];
                } else {
                    $result['message'] = 'Patient not found or Unauthorized access';
                }
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            $result['message'] = 'User not logged in';
        }

        $response = $this->resultJsonFactory->create();
        return $response->setData($result);
    }
}