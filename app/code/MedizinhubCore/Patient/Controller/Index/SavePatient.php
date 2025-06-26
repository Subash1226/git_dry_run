<?php

namespace MedizinhubCore\Patient\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use MedizinhubCore\Patient\Model\PatientFactory;
use Magento\Framework\Exception\LocalizedException;

class SavePatient extends Action
{
    protected $resultJsonFactory;
    protected $patientFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PatientFactory $patientFactory,
        Session $customerSession,
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->patientFactory = $patientFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();
        try {
            $customerId = $this->customerSession->getCustomer()->getId();
            // if (!$customerId) {
            //     throw new LocalizedException(__('Customer not logged in'));
            // }

            $patient = $this->patientFactory->create();
            $patient->setCustomerId($customerId)
                ->setName($postData['firstname']. ' ' . $postData['lastname'])
                ->setEmail($postData['customer_email'])
                ->setAge($postData['customer_age'])
                ->setDateOfBirth(!empty($postData['dob']) ? $postData['dob'] : null)
                ->setBloodGroup(!empty($postData['blood_group']) ? $postData['blood_group'] : null)
                ->setHouseNo($postData['house_no'])
                ->setStreet($postData['street'])
                ->setCity($postData['city'])
                ->setArea($postData['area'])
                ->setRegion($postData['region'])
                ->setRegionId($postData['region_id'])
                ->setCountryId($postData['country_id'])
                ->setGender($postData['customer_gender'])
                ->setPostcode($postData['pincode'])
                ->setPhone($postData['telephone'])
                ->setWhatsapp($postData['whatsapp'])
                ->setStatus(1)
                ->save();

            $result->setData(['success' => true, 'message' => __('Patient information saved successfully.')]);
        } catch (LocalizedException $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => __('An error occurred while saving patient information.')
            ]);
        }
        return $result;
    }
}
