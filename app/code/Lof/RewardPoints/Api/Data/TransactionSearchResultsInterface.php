<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Api\Data;

interface TransactionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get available points
     * @return float|null
     */
    public function getAvailablePoints();

    /**
     * Set available points
     * @param float $points
     * @return $this
     */
    public function setAvailablePoints($points);
    
    /**
     * Get total earned points
     * @return float|null
     */
    public function getTotalEarnedPoints();

    /**
     * Set total earned points
     * @param float $points
     * @return $this
     */
    public function setTotalEarnedPoints($points);
    
    /**
     * Get total spent points
     * @return float|null
     */
    public function getTotalSpentPoints();

    /**
     * Set total spent points
     * @param float $points
     * @return $this
     */
    public function setTotalSpentPoints($points);

    /**
     * Get Redeem list.
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set code_id list.
     * @param \Lof\RewardPoints\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}