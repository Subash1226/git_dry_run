<?php
namespace Lof\RewardPoints\Api;

interface RewardPointManagementInterface
{
    /**
     * GET for  total point
     * @param string $customerId
     * @return mixed
     */
    public function getTotalCustomerPoints($customerId);

    /**
     * GET spending total point by customer
     * @param int|null $cartId
     * @return mixed
     */
    public function getTotalSpentPoint($cartId = null);
    
    /**
     * GET Transaction
     * @param string $customer_id
     * @return mixed
     */
    public function getTransaction($customer_id);
    
    /**
     * GET for total earn points by order id
     * @param int $order_id
     * @return mixed
     */
    public function getOrderEarnPoints($order_id);
    
    /**
     * GET for total earn points by order id
     * @param int $order_id
     * @return mixed
     */
    public function getOrderEarnSpentPoints($order_id);
    
    /**
     * GET List spend rule in cart
     * @param int $cartId
     * @return mixed
     */
    public function getListSpendingRule($cartId);
    
    /**
     * apply/cancel points in cart
     * @param int|null $cartId
     * @param string $action (apply|cancel)
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyPoint($action, $cartId = null);
    
    /**
     * Retrieve  matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\RewardPoints\Api\Data\RewardPointsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}