<?php

namespace MedizinhubCore\Patient\Model\ResourceModel;


/**
 * Class Reviews
 * @package MedizinhubCore\Patient\Model\ResourceModel
 */
class Appointments extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('patient_appointment', 'appointment_id');
    }
}
