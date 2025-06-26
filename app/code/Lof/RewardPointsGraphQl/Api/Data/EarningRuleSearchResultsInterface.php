<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Api\Data;

interface EarningRuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get brand list.
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface[]
     */
    public function getItems();

    /**
     * Set brand_id list.
     * @param \Lof\RewardPoints\Api\Data\RewardPointsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
