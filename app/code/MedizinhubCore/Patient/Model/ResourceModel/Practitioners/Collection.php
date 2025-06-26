<?php
namespace MedizinhubCore\Patient\Model\ResourceModel\Practitioners;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MedizinhubCore\Patient\Model\Practitioners as Model;
use MedizinhubCore\Patient\Model\ResourceModel\Practitioners as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
