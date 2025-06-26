<?php

namespace MedizinhubCore\Patient\Model\Api;

use MedizinhubCore\Patient\Api\PatientInterface;
use MedizinhubCore\Patient\Api\Data\PatientDataInterface;
use MedizinhubCore\Patient\Model\ResourceModel\Patient\CollectionFactory as PatientCollectionFactory;
use MedizinhubCore\Patient\Api\Data\PatientDataInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Patient implements PatientInterface
{
    protected $patientCollectionFactory;
    protected $patientDataFactory;

    public function __construct(
        PatientCollectionFactory $patientCollectionFactory,
        PatientDataInterfaceFactory $patientDataFactory
    ) {
        $this->patientCollectionFactory = $patientCollectionFactory;
        $this->patientDataFactory = $patientDataFactory;
    }

    /**
     * Get Patients List using customerId
     *
     * @param int $customerId
     * @return \MedizinhubCore\Patient\Api\Data\PatientDataInterface[]
     * @throws NoSuchEntityException
     */
    public function getPatientsByCustomerId($customerId)
    {
        $patientCollection = $this->patientCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);

        if ($patientCollection->getSize() == 0) {
            throw new NoSuchEntityException(__('No patients found for customer ID "%1".', $customerId));
        }

        $patients = [];
        foreach ($patientCollection as $patientData) {
            /** @var \MedizinhubCore\Patient\Api\Data\PatientDataInterface $patient */
            $patient = $this->patientDataFactory->create();
            $patient->setName($patientData->getName());
            $patient->setEmail($patientData->getEmail());
            $patient->setAge($patientData->getAge());
            $patient->setHouseNo($patientData->getHouseNo());
            $patient->setStreet($patientData->getStreet());
            $patient->setCity($patientData->getCity());
            $patient->setArea($patientData->getArea());
            $patient->setRegionId($patientData->getRegionId());
            $patient->setCountryId($patientData->getCountryId());
            $patient->setGender($patientData->getGender());
            $patient->setPostCode($patientData->getPostCode());
            $patient->setPhone($patientData->getPhone());
            $patient->setWhatsApp($patientData->getWhatsApp());
            $patient->setBloodGroup($patientData->getBloodGroup());
            $patient->setStatus($patientData->getStatus());
            $patient->setDateOfBirth($patientData->getDateofBirth());
            $patients[] = $patient;
        }

        return $patients;
    }
}
