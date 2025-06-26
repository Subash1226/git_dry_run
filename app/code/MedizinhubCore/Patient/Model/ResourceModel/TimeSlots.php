<?php
namespace MedizinhubCore\Patient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TimeSlots extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('time_slots', 'id'); // table name and primary key
    }
}
