<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Model\Resolver;

use Lof\RewardPoints\Model\Transaction;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\RewardPoints\Model\Customer as CustomerRewardPoints;
use Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class Customer
 * @package Lof\RewardPointsGraphQl\Model\Resolver
 */
class Customer implements ResolverInterface
{


    /**
     * @var CustomerRewardPoints
     */
    private $customer;
    /**
     * @var CollectionFactory
     */
    private $transactionCollection;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * EarningRules constructor.
     * @param CustomerRewardPoints $customer
     * @param CollectionFactory $collectionFactory
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        CustomerRewardPoints $customer,
        CollectionFactory $collectionFactory,
        GetCustomer $getCustomer
    )
    {
        $this->customer = $customer;
        $this->getCustomer = $getCustomer;
        $this->transactionCollection = $collectionFactory;
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
        $customer = $this->getCustomer->execute($context);
        $args['customer_id'] = $customer->getId();
        $customer = $this->customer->load($args['customer_id'],'customer_id');
        $transactions = $this->transactionCollection->create()
            ->addFieldToFilter('customer_id',$args['customer_id'])
            ->addFieldToFilter(['action', 'status'],[
                ['neq' => Transaction::ADMIN_ADD_TRANSACTION],
                ['neq' => Transaction::STATE_PROCESSING]
            ]);
        $data = $customer->getData();
        $data['transactions']['items'] = $transactions->getData();
        $data['transactions']['total_count'] = $transactions->getSize();
        return $data;
    }
}
