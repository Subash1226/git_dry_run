<?php

namespace MedizinhubCore\Patient\Model\ResourceModel\RazorpayPayment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\RazorpayPayment;
use MedizinhubCore\Patient\Model\ResourceModel\RazorpayPayment as RazorpayPaymentResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(RazorpayPayment::class, RazorpayPaymentResource::class);
    }
}
