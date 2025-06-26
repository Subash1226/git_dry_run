<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Model;

use Lof\RewardPoints\Helper\Balance\Earn;
use Lof\RewardPointsGraphQl\Api\EarningRuleRepositoryInterface;
use Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory;
use Lof\RewardPointsGraphQl\Api\Data\EarningRuleSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class EarningRuleRepository
 * @package Lof\RewardPointsGraphQl\Model
 */
class EarningRuleRepository implements EarningRuleRepositoryInterface
{

    /**
     * @var EarningRuleSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;
    /**
     * @var CollectionFactory
     */
    private $collection;
    /**
     * @var Earn
     */
    private $rewardsBalanceEarn;

    /**
     * EarningRuleRepository constructor.
     * @param CollectionFactory $earningCollection
     * @param EarningRuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param Earn $rewardsBalanceEarn
     */
    public function __construct(
        CollectionFactory $earningCollection,
        EarningRuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        Earn $rewardsBalanceEarn
    ) {
        $this->collection = $earningCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;

    }


    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria,
        $customerGroupId,
        $storeId
    ) {
        $collection = $this->rewardsBalanceEarn->getRules('',$storeId, $customerGroupId);

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $key => $model) {
            $items[$key] = $model->getData();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
