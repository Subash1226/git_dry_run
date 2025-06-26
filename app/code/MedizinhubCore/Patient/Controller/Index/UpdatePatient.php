<?php

namespace MedizinhubCore\Patient\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use MedizinhubCore\Patient\Model\PatientFactory;

class UpdatePatient extends Action
{
    protected $resultJsonFactory;
    protected $patientFactory;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PatientFactory $patientFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->patientFactory = $patientFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();

        try {
            if (
                isset($postData['patient_id']) &&
                isset($postData['first_name']) &&
                isset($postData['last_name']) &&
                isset($postData['customer_age']) &&
                isset($postData['customer_gender']) &&
                isset($postData['houseNo']) &&
                isset($postData['street']) &&
                isset($postData['area']) &&
                isset($postData['city']) &&
                isset($postData['pincode']) &&
                isset($postData['telephone']) &&
                isset($postData['whatsapp']) &&
                isset($postData['customer_email']) &&
                isset($postData['regionId'])
            ) {
                $patientId = $postData['patient_id'];
                $name = $postData['first_name'] . ' ' . $postData['last_name'];
                $customerAge = $postData['customer_age'];
                $customerGender = $postData['customer_gender'];
                $dob = $postData['dob'];
                $bloodGroup = $postData['blood_group'];
                $houseNo = $postData['houseNo'];
                $street = $postData['street'];
                $area = $postData['area'];
                $city = $postData['city'];
                $pincode = $postData['pincode'];
                $telephone = $postData['telephone'];
                $whatsapp = $postData['whatsapp'];
                $email = $postData['customer_email'];
                $regionId = $postData['regionId'];

                $patientModel = $this->patientFactory->create()->load($patientId);
                if ($patientModel->getId()) {
                    $patientModel->setName($name)
                        ->setAge($customerAge)
                        ->setGender($customerGender)
                        ->setDateOfBirth(!empty($dob) ? $dob : null)
                        ->setBloodGroup(!empty($bloodGroup) ? $bloodGroup : null)
                        ->setHouseNo($houseNo)
                        ->setStreet($street)
                        ->setArea($area)
                        ->setCity($city)
                        ->setPincode($pincode)
                        ->setTelephone($telephone)
                        ->setWhatsapp($whatsapp)
                        ->setEmail($email)
                        ->setRegionId($regionId)
                        ->save();

                    $this->logger->info('Received data:', $postData);

                    $result->setData(['success' => true]);
                } else {
                    $result->setData(['success' => false, 'message' => 'Patient not found']);
                }
            } else {
                $result->setData(['success' => false, 'message' => 'Missing required fields']);
            }
        } catch (LocalizedException $e) {
            $this->logger->error('Error updating patient:', ['exception' => $e]);
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->critical('Critical error updating patient', ['exception' => $e]);
            $result->setData([
                'success' => false,
                'message' => __('An error occurred while updating the patient. Please try again later.')
            ]);
        }
        return $result;
    }
}
