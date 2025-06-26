<?php
namespace MedizinhubCore\Patient\Model\ResourceModel\Hospitals;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\Hospitals as Model;
use MedizinhubCore\Patient\Model\ResourceModel\Hospitals as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
