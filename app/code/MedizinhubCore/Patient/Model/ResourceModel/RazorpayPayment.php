<?php

namespace MedizinhubCore\Patient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RazorpayPayment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('patient_appointment', 'appointment_id');
    }
}
