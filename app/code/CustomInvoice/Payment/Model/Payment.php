<?php
namespace CustomInvoice\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use CustomInvoice\Payment\Model\ResourceModel\Payment as ResourceModel;

class Payment extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}