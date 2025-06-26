<?php
namespace CustomInvoice\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Payment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_invoice_payment', 'entity_id');
    }
}