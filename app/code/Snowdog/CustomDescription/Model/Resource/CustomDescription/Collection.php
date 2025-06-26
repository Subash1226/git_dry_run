<?php

namespace Snowdog\CustomDescription\Model\Resource\CustomDescription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Snowdog\CustomDescription\Model\CustomDescription as CustomDescriptionModel;
use Snowdog\CustomDescription\Model\Resource\CustomDescription as CustomDescriptionResource;

/**
 * Class Collection
 * @package Snowdog\CustomDescription\Model\Resource\CustomDescription
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     *
     * This method initializes the model and resource model associated with the collection.
     */
    protected function _construct()
    {
        $this->_init(
            CustomDescriptionModel::class,
            CustomDescriptionResource::class
        );
    }

    /**
     * Add a filter by product ID to the collection.
     *
     * @param int $productId
     * @return $this
     */
    public function addProductFilter(int $productId)
    {
        $this->addFieldToFilter('product_id', $productId);
        return $this;
    }

    /**
     * Add a filter by a custom field to the collection.
     *
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addCustomFieldFilter(string $field, $value)
    {
        $this->addFieldToFilter($field, $value);
        return $this;
    }
}
