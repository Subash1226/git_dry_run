<?php
namespace SalesOrder\UserLog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderViewLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_user_log', 'log_id');
    }
}