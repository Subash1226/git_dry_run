<?php
namespace MedizinhubCore\Patient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Hospitals extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('hospitals', 'id');  // table name and primary key
    }
}
