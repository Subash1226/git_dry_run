<?php

namespace Quick\Order\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class AddressFactory
{
    protected $model;

    public function __construct(Address $model)
    {
        $this->model = $model;
    }

    public function create()
    {
        return $this->model;
    }
}
