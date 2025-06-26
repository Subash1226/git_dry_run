<?php
namespace MedizinhubCore\Patient\Model\ResourceModel\TimeSlots;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\TimeSlots as Model;
use MedizinhubCore\Patient\Model\ResourceModel\TimeSlots as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
