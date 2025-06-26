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
 * @package    Lof_Gallery
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\TransactionRepositoryInterface;
use Lof\RewardPoints\Api\Data\TransactionInterfaceFactory;
use Lof\RewardPoints\Api\Data\TransactionSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\RewardPoints\Model\TransactionFactory;
use Lof\RewardPoints\Model\ResourceModel\Transaction as ResourceTransaction;
use Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Model\Email;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;
    
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $rewardpointsCollection;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    protected $resource;

    protected $transactionFactory;

    protected $transactionCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataTransactionFactory;

    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    protected $helperData;
    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @param ResourceTransaction $resource
     * @param UserContextInterface $userContext
     * @param TransactionFactory $transactionFactory
     * @param TransactionInterfaceFactory $dataTransactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param TransactionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param \Lof\RewardPoints\Helper\Data $helperData
     * @param \Lof\RewardPoints\Helper\Mail $rewardsMail
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $rewardpointsCollection
     */
    public function __construct(
        UserContextInterface $userContext,
        ResourceTransaction $resource,
        TransactionFactory $transactionFactory,
        TransactionInterfaceFactory $dataTransactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        \Lof\RewardPoints\Helper\Data $helperData,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $rewardpointsCollection
    ) {
        $this->userContext = $userContext;
        $this->resource = $resource;
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTransactionFactory = $dataTransactionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->_config = $config;
        $this->helperData = $helperData;
        $this->rewardsMail = $rewardsMail;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->transactionRepository = $this;
        $this->rewardpointsCollection = $rewardpointsCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\RewardPoints\Api\Data\TransactionInterface $transaction
    ) {
        try {
            $this->resource->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transaction: %1',
                $exception->getMessage()
            ));
        }
        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction with id "%1" does not exist.', $transactionId));
        }
        
        // Add order ID to the response data if available
        if ($transaction->getOrderId()) {
            $transaction->setData('order_id', $transaction->getOrderId());
        }
        
        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->transactionCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        
        // Ensure order_id is included in each item
        $items = [];
        foreach ($collection->getItems() as $item) {
            if ($item->getOrderId()) {
                $item->setData('order_id', $item->getOrderId());
            }
            $items[] = $item;
        }
        
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\RewardPoints\Api\Data\TransactionInterface $transaction
    ) {
        try {
            $this->resource->delete($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the transaction: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($transactionId)
    {
        return $this->delete($this->getById($transactionId));
    }

    /**
     * {@inheritdoc}
     */
    public function cancelById($transactionId)
    {
        $transaction = $this->getById($transactionId);
        $status = $transaction->getStatus();
        $amount = $transaction->getAmount();
        $customer        = $transaction->getRewardsCustomer();
        $availablePoints = $customer->getAvailablePoints();
        $totalPoints     = $customer->getTotalPoints();
        if ($transaction->getStatus() == Transaction::STATE_COMPLETE && $amount > 0 && $transaction->getAmount() > $availablePoints) {
            throw new NoSuchEntityException(__('Account points is not enough points to cancel the transaction.'));
        }

        $transaction->setStatus(Transaction::STATE_CANCELED);
        $transaction->save();

        if ($status == Transaction::STATE_COMPLETE) {
            $params['title'] = __('Admin cancel the transaction #%1', $transaction->getId());
            $trigger = Email::ACTION_CANCEL_EARNED_POINTS;
            if ($amount < 0) {
                $trigger = Email::ACTION_CANCEL_SPENT_POINTS;
            }
            $amount = -$amount;
            $params['transaction_amount'] = $this->helperData->formatPoints($amount); 
            $this->rewardsMail->setTrigger($trigger)
                            ->setParams($params)
                            ->sendNotificationBalanceUpdateEmail($transaction);
        }
        $customer->refreshPoints()->save();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMyList(
        $customerId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ){
        if(!$customerId)
            throw new NoSuchEntityException(__('Please logged in your account.'));
        
        $collection = $this->transactionCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $collection->addFieldtoFilter("customer_id", $customerId);
        
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        
        // Ensure order_id is included in each item in customer's transaction list
        $items = [];
        foreach ($collection->getItems() as $item) {
            if ($item->getOrderId()) {
                $item->setData('order_id', $item->getOrderId());
            }
            $items[] = $item;
        }
        
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getMyTransactionById($customerId, $transactionId){
        if(!$customerId)
            throw new NoSuchEntityException(__('Please logged in your account.'));
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId);
        if (!$transaction->getId() || ($customerId != $transaction->getCustomerId())) {
            throw new NoSuchEntityException(__('Transaction with id "%1" does not exist.', $transactionId));
        }
        
        // Add order ID to the customer's transaction response if available
        if ($transaction->getOrderId()) {
            $transaction->setData('order_id', $transaction->getOrderId());
        }
        
        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function getMyAllTransactions()
    {
        try {
            $customerId = $this->getCurrentCustomerId();

            // Get available points
            $availablePoints = 0;
            $customerCollection = $this->rewardpointsCollection->create();
            $customerCollection->addFieldToFilter('customer_id', $customerId);
            
            foreach ($customerCollection->getItems() as $points) {
                $availablePoints = (float)$points->getData('total_points');
                break;
            }

            // Get transactions
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->transactionRepository->getMyList($customerId, $searchCriteria);
            $transactions = $searchResults->getItems();

            // Group transactions by order_id
            $groupedTransactions = [];
            $totalEarnedPoints = 0;
            $totalSpentPoints = 0;
            
            foreach ($transactions as $transaction) {
                $orderId = $transaction->getOrderId();
                
                // Skip transactions with no order ID
                if (!$orderId) {
                    continue;
                }
                
                // Only include transactions that are applied and have complete status
                // if ($transaction->getIsApplied() == 1 && $transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    if (!isset($groupedTransactions[$orderId])) {
                        $groupedTransactions[$orderId] = [
                            'order_id' => $orderId,
                            'customer_id' => $transaction->getCustomerId(),
                            'earn_points' => 0,
                            'spent_points' => 0,
                            'title' => $transaction->getTitle(),
                            'status' => $transaction->getStatus(),
                            'updated_at' => $transaction->getUpdatedAt(),
                            'created_at' => $transaction->getCreatedAt(),
                            'expires_at' => $transaction->getExpiresAt(),
                            'is_applied' => $transaction->getIsApplied()
                        ];
                    }
                    
                    // Add points based on transaction action
                    if ($transaction->getAction() == 'earning_order') {
                        $earnPoints = (float)$transaction->getAmount();
                        $groupedTransactions[$orderId]['earn_points'] = $earnPoints;
                        if ($transaction->getIsApplied() == 1 && $transaction->getStatus() == Transaction::STATE_COMPLETE) {
                            $totalEarnedPoints += $earnPoints;
                        }
                    } elseif ($transaction->getAction() == 'spending_order') {
                        $spentPoints = abs((float)$transaction->getAmount());
                        $groupedTransactions[$orderId]['spent_points'] = $spentPoints;
                        if ($transaction->getIsApplied() == 1 && $transaction->getStatus() == Transaction::STATE_COMPLETE) {
                            $totalSpentPoints += $spentPoints;
                        }
                    }
                    
                    // Use the latest updated_at date
                    if (strtotime($transaction->getUpdatedAt()) > strtotime($groupedTransactions[$orderId]['updated_at'])) {
                        $groupedTransactions[$orderId]['updated_at'] = $transaction->getUpdatedAt();
                    }
                    
                    // Use the latest expires_at date if available
                    if ($transaction->getExpiresAt() && 
                        (!$groupedTransactions[$orderId]['expires_at'] || 
                        strtotime($transaction->getExpiresAt()) > strtotime($groupedTransactions[$orderId]['expires_at']))) {
                        $groupedTransactions[$orderId]['expires_at'] = $transaction->getExpiresAt();
                    }
                // }
            }

            // Create a new search results object with merged transactions
            $customSearchResults = $this->searchResultsFactory->create();
            $customSearchResults->setSearchCriteria($searchCriteria);
            $customSearchResults->setItems(array_values($groupedTransactions));
            $customSearchResults->setTotalCount(count($groupedTransactions));
            $customSearchResults->setAvailablePoints($availablePoints);
            $customSearchResults->setTotalEarnedPoints($totalEarnedPoints);
            $customSearchResults->setTotalSpentPoints($totalSpentPoints);

            return $customSearchResults;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get current customer ID from API token
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCurrentCustomerId(): int
    {
        $customerId = $this->userContext->getUserId();
    
        if (!$customerId || $this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Customer is not logged in'),
                null,
                401
            );
        }
        return $customerId;
    }
}