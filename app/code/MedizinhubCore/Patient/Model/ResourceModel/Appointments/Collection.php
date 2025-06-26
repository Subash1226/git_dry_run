<?php

namespace MedizinhubCore\Patient\Model\ResourceModel\Appointments;
/**
 * Class Collection
 * @package MedizinhubCore\Sample\Model\ResourceModel\Appointments
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'appointment_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'customer_patient_appointments_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'appointments_collection';

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Patient\Model\Appointments', 'MedizinhubCore\Patient\Model\ResourceModel\Appointments');

    }
}
