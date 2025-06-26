<?php
namespace MedizinhubCore\Sample\Model\ResourceModel;


/**
 * Class Reviews
 * @package MedizinhubCore\Sample\Model\ResourceModel
 */
class Manage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     */
    protected function _construct() {
        $this->_init('customer_labtest', 'test_id');
    }
}
