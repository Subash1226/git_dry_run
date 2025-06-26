<?php

namespace MedizinhubCore\Sample\Model;

use Magento\Framework\Model\AbstractModel;

class CustomerLabtest extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('MedizinhubCore\Sample\Model\ResourceModel\CustomerLabtest');
    }
}
