<?php

namespace MedizinhubCore\Patient\Model\ResourceModel\Patient;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\Patient as PatientModel;
use MedizinhubCore\Patient\Model\ResourceModel\Patient as PatientResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            PatientModel::class,
            PatientResourceModel::class
        );
    }
}
