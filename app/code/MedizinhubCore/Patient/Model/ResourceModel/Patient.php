<?php

namespace MedizinhubCore\Patient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Patient extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('patient', 'id');
    }
}
