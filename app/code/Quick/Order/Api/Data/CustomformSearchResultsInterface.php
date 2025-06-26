<?php
declare(strict_types=1);

namespace Quick\Order\Api\Data;

interface CustomformSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Customform list.
     * @return \Quick\Order\Api\Data\CustomformInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Quick\Order\Api\Data\CustomformInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

