<?php

namespace Lof\RewardPoints\Plugin\Magento\Sales\Api;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class OrderRepositoryInterfacePlugin
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Add reward points discount data to order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        try {
            $this->addRewardPointsDiscount($order);
        } catch (\Exception $e) {
            $this->logger->error('Error adding reward points discount to order: ' . $e->getMessage(), [
                'order_id' => $order->getIncrementId(),
                'exception' => $e
            ]);
        }
        return $order;
    }

    /**
     * Add reward points discount data to order search results
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        try {
            $orders = $searchResult->getItems();
            
            foreach ($orders as $order) {
                $this->addRewardPointsDiscount($order);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error adding reward points discount to order list: ' . $e->getMessage());
        }
        
        return $searchResult;
    }

    /**
     * Add reward points discount data to order
     *
     * @param OrderInterface $order
     * @return void
     */
    private function addRewardPointsDiscount($order)
    {
        $orderId = $order->getEntityId();
        if (!$orderId) {
            return;
        }

        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('lof_rewardpoints_purchase');
            
            $sql = "SELECT discount, spend_points FROM {$table} 
                WHERE order_id = {$orderId}
                ORDER BY discount DESC, spend_points DESC
                LIMIT 1";
            $result = $connection->fetchRow($sql);
            
            if ($result && !empty($result['discount'])) {
                $extensionAttributes = $order->getExtensionAttributes();
                if ($extensionAttributes) {
                    // Round off the discount to an integer value and ensure it's positive
                    $roundedDiscount = round(abs((float)$result['discount']));
                    $extensionAttributes->setRewardPointsDiscount($roundedDiscount);
                    
                    // Round off spent points to an integer as well
                    $spentPoints = round((float)$result['spend_points']);
                    $extensionAttributes->setSpentRewardPoints($spentPoints);
                }
            }
        } catch (\Exception $e) {
            throw $e; // Re-throw to be caught by the parent method
        }
    }
}