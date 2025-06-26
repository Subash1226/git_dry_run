<?php

namespace MedizinhubCore\Patient\Model\ResourceModel\PatientAppointment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\PatientAppointment as Model;
use MedizinhubCore\Patient\Model\ResourceModel\Appointments as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
