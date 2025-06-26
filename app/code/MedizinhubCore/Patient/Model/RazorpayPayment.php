<?php

namespace MedizinhubCore\Patient\Model;

use Magento\Framework\Model\AbstractModel;
use MedizinhubCore\Patient\Model\ResourceModel\RazorpayPayment as RazorpayPaymentResource;

class RazorpayPayment extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(RazorpayPaymentResource::class);
    }
}
