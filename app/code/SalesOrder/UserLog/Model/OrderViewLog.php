<?php
namespace SalesOrder\UserLog\Model;

use Magento\Framework\Model\AbstractModel;

class OrderViewLog extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\SalesOrder\UserLog\Model\ResourceModel\OrderViewLog::class);
    }
}