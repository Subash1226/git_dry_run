<?php

namespace MedizinhubCore\Sample\Ui\DataProvider\Manage;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package MageDigest\Grid\Ui\DataProvider\Category\Listing
 */
class Collection extends SearchResult
{
    /**
     * Override _initSelect to add custom columns
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('test_id', 'main_table.test_id');
        parent::_initSelect();
    }
}
