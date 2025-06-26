<?php

namespace MedizinhubCore\Patient\Api;

interface PatientInterface
{
    /**
     * Get Patients List using customerId
     *
     * @param int $customerId
     * @return \MedizinhubCore\Patient\Api\Data\PatientDataInterface[]
     */
    public function getPatientsByCustomerId($customerId);
}
