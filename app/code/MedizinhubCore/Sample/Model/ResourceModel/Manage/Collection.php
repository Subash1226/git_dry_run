<?php

namespace MedizinhubCore\Sample\Model\ResourceModel\Manage;
/**
 * Class Collection
 * @package MedizinhubCore\Sample\Model\ResourceModel\Manage
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'test_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'customer_labtest_manage_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'manage_collection';

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Sample\Model\Manage', 'MedizinhubCore\Sample\Model\ResourceModel\Manage');

    }
}
