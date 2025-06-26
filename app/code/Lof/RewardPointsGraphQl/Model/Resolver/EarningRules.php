<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\RewardPointsGraphQl\Api\EarningRuleRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class EarningRules
 * @package Lof\RewardPointsGraphQl\Model\Resolver
 */
class EarningRules implements ResolverInterface
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var EarningRuleRepositoryInterface
     */
    private $earningRuleRepository;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * EarningRules constructor.
     * @param EarningRuleRepositoryInterface $earningRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        EarningRuleRepositoryInterface $earningRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetCustomer $getCustomer
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->earningRuleRepository = $earningRuleRepository;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['storeId'])) {
            throw new GraphQlInputException(__('"storeId" value should be specified'));
        }
        $customer = $this->getCustomer->execute($context);
        $args['customer_group_id'] = $customer->getGroupId();
        $searchCriteria = $this->searchCriteriaBuilder->build( 'lof_rewardpoints_earning_rule', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->earningRuleRepository->getList( $searchCriteria, $args['customer_group_id'], $args['storeId']);

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $searchResult->getItems(),
        ];
    }
}
