<?php
// app/code/Quick/Order/Model/ResourceModel/Address.php

namespace Quick\Order\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Address extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('customer_address_entity', 'entity_id');
    }
}
