<?php

namespace Quick\Order\Model;

use Magento\Framework\Model\AbstractModel;

class Address extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Quick\Order\Model\ResourceModel\Address');
    }
}
