<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\RewardPointsGraphQl\Model;

use Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory;
use Lof\RewardPointsGraphQl\Api\Data\EarningRuleSearchResultsInterfaceFactory;
use Lof\RewardPointsGraphQl\Api\SpendingRuleRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\StoreManagerInterface;
use Lof\RewardPoints\Helper\Balance\Spend;


/**
 * Class SpendingRuleRepository
 * @package Lof\RewardPointsGraphQl\Model
 */
class SpendingRuleRepository implements SpendingRuleRepositoryInterface
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
     * @var Spend
     */
    private $spend;
    /**
     * @var Spend
     */
    private $spendHelper;

    /**
     * EarningRuleRepository constructor.
     * @param CollectionFactory $spendingCollection
     * @param EarningRuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param Spend $spendHelper
     */
    public function __construct(
        CollectionFactory $spendingCollection,
        EarningRuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        Spend $spendHelper
    ) {
        $this->collection = $spendingCollection;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->spendHelper = $spendHelper;

    }


    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria,
        $customerGroupId,
        $storeId
    ) {
        $collection = $this->spendHelper->getRules($storeId, $customerGroupId);

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
