<?php
namespace MedizinhubCore\Patient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DoctorComment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('doctor_comment', 'id'); // `doctor_comment` is the table name, `comment_id` is the primary key
    }
}
