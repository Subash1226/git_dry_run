<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface EarningRuleRepositoryInterface
 * @package Lof\RewardPointsGraphQl\Api
 */
interface EarningRuleRepositoryInterface
{

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $customerGroupId
     * @param $storeId
     * @return mixed
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        $customerGroupId,
        $storeId
    );

}
