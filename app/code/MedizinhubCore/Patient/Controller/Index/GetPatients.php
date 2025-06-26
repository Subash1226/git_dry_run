<?php

namespace MedizinhubCore\Patient\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;

class GetPatients extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $connection;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $customerSession, // Ensure this is correct
        ResourceConnection $resourceConnection // Ensure this is correct
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->connection = $resourceConnection->getConnection();
    }

    public function execute()
    {
        $customerId = $this->customerSession->getCustomer()->getId();

        $result1 = $this->connection->fetchAll(
            "SELECT id, customer_id, name, email, age, date_of_birth, blood_group, category, house_no, street, city, area, region_id, country_id, gender, postcode, phone, whatsapp, created_at, updated_at 
            FROM patient 
            WHERE customer_id = :customer_id 
            ORDER BY id DESC",
            ['customer_id' => (int)$customerId]
        );
        
        $formattedPatients = '';
        $firstPatient = true;

        foreach ($result1 as $patient) {
            $checked = $firstPatient ? 'checked' : '';

            $nameParts = explode(' ', $patient['name']);
            $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            $formattedDob = '';
            if (!empty($patient['date_of_birth'])) {
                $date = new \DateTime($patient['date_of_birth']);
                $formattedDob = $date->format('d/m/Y');
            }

            // Safely handle the pincode field
            $lastTwoDigitsOfPincode = isset($patient['postcode']) ? substr($patient['postcode'], -2) : '00';

            $formattedPatients .= '<div class="patient-preview" data-patient-id="' . $patient['id'] . '">';
            $formattedPatients .= '<hr class="patient-break-line">';
            $formattedPatients .= '<input type="radio" name="patient_entity" value="' . $patient['id'] . '" data-patient-id="' . $patient['id'] . '" ' . $checked . '>';
            $formattedPatients .= '<br><strong>' . ucfirst($firstName) . ' ' . ucfirst($lastName) . ' ( Age : ' . $patient['age'] . ') (' . ucfirst($patient['gender']) . ')</strong> ';
            $formattedPatients .= $patient['house_no'] . ', ' . $patient['street'] . ', ' . $patient['area'] . ', Chennai-[Ch-' . $lastTwoDigitsOfPincode . ']<br>';

            $formattedPatients .= 'Blood Group: ' . $patient['blood_group'] . ', Ph.No: ' . $patient['phone'] . ', WhatsApp.No: ' . $patient['whatsapp'] . ', Email.Id: ' . $patient['email'] . '<br>';
            $formattedPatients .= '<a href="#" class="edit-patient" 
                data-patient-id="' . $patient['id'] . '" 
                data-firstname="' . $firstName . '" 
                data-lastname="' . $lastName . '" 
                data-age="' . $patient['age'] . '" 
                data-gender="' . $patient['gender'] . '" 
                data-house-no="' . $patient['house_no'] . '" 
                data-area="' . $patient['area'] . '" 
                data-street="' . $patient['street'] . '" 
                data-city="' . $patient['city'] . '" 
                data-pincode="' . $patient['postcode'] . '" 
                data-telephone="' . $patient['phone'] . '" 
                data-whatsapp="' . $patient['whatsapp'] . '" 
                data-region-id="' . $patient['region_id'] . '" 
                data-email="' . $patient['email'] . '" 
                data-dob="' . $patient['date_of_birth'] . '" 
                data-blood-group="' . $patient['blood_group'] . '">Edit</a> | ';
            $formattedPatients .= '<a href="#" class="delete-patient" data-patient-id="' . $patient['id'] . '" onclick="patientChecker()">Delete</a><br><br>';
            $formattedPatients .= '</div>';

            $firstPatient = false;
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($formattedPatients);
    }
}
