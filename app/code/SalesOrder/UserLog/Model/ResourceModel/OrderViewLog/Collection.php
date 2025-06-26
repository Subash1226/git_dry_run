<?php
namespace SalesOrder\UserLog\Model\ResourceModel\OrderViewLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \SalesOrder\UserLog\Model\OrderViewLog::class,
            \SalesOrder\UserLog\Model\ResourceModel\OrderViewLog::class
        );
    }
}