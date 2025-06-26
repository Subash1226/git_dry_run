<?php
namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\Data\TransactionSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class TransactionSearchResults extends SearchResults implements TransactionSearchResultsInterface
{
    /**
     * @var float|null
     */
    protected $availablePoints;

    /**
     * @var float|null
     */
    protected $totalEarnedPoints;

    /**
     * @var float|null
     */
    protected $totalSpentPoints;

    /**
     * Get available points
     * @return float|null
     */
    public function getAvailablePoints()
    {
        return $this->availablePoints;
    }

    /**
     * Set available points
     * @param float $points
     * @return $this
     */
    public function setAvailablePoints($points)
    {
        $this->availablePoints = $points;
        return $this;
    }

    /**
     * Get total earned points
     * @return float|null
     */
    public function getTotalEarnedPoints()
    {
        return $this->totalEarnedPoints;
    }

    /**
     * Set total earned points
     * @param float $points
     * @return $this
     */
    public function setTotalEarnedPoints($points)
    {
        $this->totalEarnedPoints = $points;
        return $this;
    }

    /**
     * Get total spent points
     * @return float|null
     */
    public function getTotalSpentPoints()
    {
        return $this->totalSpentPoints;
    }

    /**
     * Set total spent points
     * @param float $points
     * @return $this
     */
    public function setTotalSpentPoints($points)
    {
        $this->totalSpentPoints = $points;
        return $this;
    }
}