<?php
namespace MedizinhubCore\Patient\Model\ResourceModel\DoctorComment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Patient\Model\DoctorComment', 'MedizinhubCore\Patient\Model\ResourceModel\DoctorComment');
    }
}
