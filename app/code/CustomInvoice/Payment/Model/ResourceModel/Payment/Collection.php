<?php
namespace CustomInvoice\Payment\Model\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CustomInvoice\Payment\Model\Payment as Model;
use CustomInvoice\Payment\Model\ResourceModel\Payment as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}