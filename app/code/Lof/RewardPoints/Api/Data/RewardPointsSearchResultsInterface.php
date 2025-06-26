<?php

namespace Lof\RewardPoints\Api\Data;

interface RewardPointsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get list.
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface[]
     */
    public function getItems();

    /**
     * Set  list.
     * @param \Lof\RewardPoints\Api\Data\RewardPointsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
    /**
     * Get search criteria.
     *
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function getSearchCriteria();

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount();

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount);
    
    /**
     * Get customer's available points
     * @return float
     */
    public function getAvailablePoints();
    
    /**
     * Set customer's available points
     * @param float $points
     * @return $this
     */
    public function setAvailablePoints($points);
}