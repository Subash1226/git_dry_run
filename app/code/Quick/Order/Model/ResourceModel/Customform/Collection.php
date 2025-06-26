<?php

declare(strict_types=1);

namespace Quick\Order\Model\ResourceModel\Customform;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Quick\Order\Model\Customform::class,
            \Quick\Order\Model\ResourceModel\Customform::class
        );
    }
}
