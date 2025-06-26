<?php
namespace MedizinhubCore\Patient\Model;

use Magento\Framework\Model\AbstractModel;

class Practitioners extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MedizinhubCore\Patient\Model\ResourceModel\Practitioners::class);
    }
}
