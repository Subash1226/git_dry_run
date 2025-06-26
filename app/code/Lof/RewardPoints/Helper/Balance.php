<?php

/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Helper;

use Lof\RewardPoints\Logger\Logger;
use Lof\RewardPoints\Model\PurchaseFactory;
use Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Model\TransactionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Balance extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var PurchaseFactory
     */
    protected $purchaseFactory;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var Customer
     */
    protected $rewardsCustomer;

    /**
     * @var Logger
     */
    protected $rewardsLogger;

    /**
     * @var Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $rewardPointCustomerFactory;

    /**
     * @param Context                               $context
     * @param DateTime                         $dateTime
     * @param PurchaseFactory                             $purchaseFactory
     * @param TransactionFactory                          $transactionFactory
     * @param CollectionFactory $transactionCollectionFactory
     * @param Customer $rewardsCustomer
     * @param Logger                                     $rewardsLogger
     * @param Mail $rewardsMail
     */
    public function __construct(
        Context                                                          $context,
        DateTime                                                         $dateTime,
        PurchaseFactory                                                  $purchaseFactory,
        TransactionFactory                                               $transactionFactory,
        CollectionFactory                                                $transactionCollectionFactory,
        Customer                                                         $rewardsCustomer,
        Logger                                                           $rewardsLogger,
        Mail                                                             $rewardsMail,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $rewardPointCustomerFactory
    ) {
        parent::__construct($context);
        $this->dateTime                     = $dateTime;
        $this->purchaseFactory              = $purchaseFactory;
        $this->transactionFactory           = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardsCustomer              = $rewardsCustomer;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardPointCustomerFactory  = $rewardPointCustomerFactory;
    }

    /**
     * @param $order
     * @param $action
     * @return mixed
     */
    public function getByOrder($order, $action = '')
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getIncrementId());

        if ($action) {
            $collection->addFieldToFilter('action', $action);
        }

        $balance = $collection->getFirstItem();
        return $balance;
    }

    /**
     * @param $field
     * @param $value
     * @return mixed
     */
    public function getTransaction($field, $value)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter($field, $value);
        $transaction = $collection->getFirstItem();
        return $transaction;
    }

    /**
     * @return void
     */
    public function proccessTransaction()
    {
        $this->proccessTransactionApplied();
        $this->proccessTransactionExpired();
        return $this;
    }

    /**
     * @param $customer
     * @param $spentPoints
     * @return $this
     */
    public function updatePointsUsed($customer, $spentPoints)
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $transactions  = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customer->getCustomerId())
                ->addFieldToFilter('is_expired', [
                    ['eq' => 0],
                    ['null' => true]
                ])
                ->addFieldToFilter('expires_at', ['gteq' => $currentTime]);
            $transactions->getSelect()->where('amount > amount_used OR amount_used IS NULL');
            $transactions->getSelect()->order('expires_at ASC');

            //get total amount of all transactions has status is spending close
            $spending_close_transactions  = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customer->getCustomerId())
                ->addFieldToFilter('status', Transaction::SPENDING_CLOSED);
            $spending_close_transactions->getSelect()->where('amount > amount_used OR amount_used IS NULL');
            $spending_close_transactions->getSelect()->columns(['amount_total' => new \Zend_Db_Expr('SUM(amount)')])->group('customer_id');

            $spending_close_point = 0;
            if ($spending_close_transactions->getSize()) {
                $spending_close_point = (float)$spending_close_transactions->getFirstItem()->getAmountTotal();
                //$this->rewardsLogger->addError(__('spending_close_point = ') . $spending_close_point);
            }

            foreach ($transactions as $transaction) {
                $amount     = $transaction->getAmount();
                $amountUsed = $transaction->getAmountUsed();
                if (($amount - $amountUsed) < $spentPoints) {
                    $amount = $amount - $spending_close_point;
                    $amount = ($amount > 0) ? $amount : 0;
                    $transaction->setAmountUsed($amount);
                    $spentPoints -= ($amount - $amountUsed);
                } else {
                    $amount = ($amountUsed + $spentPoints) - $spending_close_point;
                    $amount = ($amount > 0) ? $amount : 0;
                    $transaction->setAmountUsed($amount);
                    $spentPoints = 0;
                }
                //$this->rewardsLogger->addError(__('updated_amount = ') . $amount);
                $transaction->save();

                if (!$spentPoints) {
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS4:' . $e->getMessage()));
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function proccessTransactionExpired()
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $collection  = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('is_expired', 0)
                ->addFieldToFilter('expires_at', ['lt' => $currentTime]);
            foreach ($collection as $transaction) {
                /**
                 * Send Email Notification
                 */
                $this->rewardsMail->sendNotificationBalanceExpiredEmail($transaction);

                /**
                 * Transaction
                 */
                $transaction->setData('is_expired', 1)
                    ->setData('status', Transaction::STATE_EXPIRED)
                    ->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS3:' . $e->getMessage()));
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function proccessTransactionApplied()
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $collection  = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('is_applied', 0)
                ->addFieldToFilter('apply_at', ['lt' => $currentTime]);
            foreach ($collection as $transaction) {
                /**
                 * Send Email Notification
                 */
                $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction);

                // Calculatation Avaiable Points for Customer
                $customer = $this->rewardPointCustomerFactory->create()->addFieldToFilter('customer_id', $transaction->getCustomerId())->getFirstItem();
                $totalPoints     = $customer->getTotalPoints() + $transaction->getAmount();
                $availablePoints = $customer->getAvailablePoints() + $transaction->getAmount();
                $customer->setAvailablePoints($availablePoints);
                $customer->setTotalPoints($totalPoints);
                $customer->save();

                /**
                 * Transaction
                 */
                $transaction->setData('is_applied', 1)
                    ->setData('status', Transaction::STATE_COMPLETE)
                    ->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS4:' . $e->getMessage()));
        }
        return $this;
    }

    /**
     * @param $params
     * @return false
     */
    public function changePointsBalance($params)
    {
        try {
            // Check exit customer & code
            if (isset($params['code']) && $params['code'] && (!$this->getTransaction('customer_id', $params['customer_id']))) {
                return false;
            }
            $collection = $this->transactionCollectionFactory->create();
            if (isset($params['transaction_id'])) {
                $collection->addFieldToFilter('transaction_id', $params['transaction_id']);
            }
            if (isset($params['customer_id'])) {
                $collection->addFieldToFilter('customer_id', $params['customer_id']);
            }
            if (isset($params['code'])) {
                $collection->addFieldToFilter('code', $params['code']);
            }
            $transaction = $collection->getFirstItem();
            if (!$transaction->getId()) {
                $transaction = $this->transactionFactory->create()->setCustomerId($params['customer_id']);
            }
            foreach ($params as $k => $v) {
                $transaction->setData($k, $v);
            }
            $transaction->save();
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }
}
