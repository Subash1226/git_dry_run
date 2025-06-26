<?php
namespace Lof\Subtotal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Lof\Subtotal\Helper\Data;

class UpdatePoints implements ObserverInterface
{
    protected $logger;
    protected $helper;

    public function __construct(LoggerInterface $logger,Data $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;

    }

    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            $this->logger->info('Subtotal reward points feature is disabled. Skipping points creation.');
            return $this;
        }
        try {
            $order = $observer->getEvent()->getOrder();
            if (!$order || !$order->getCustomerId()) {
                return $this;
            }

            // Only proceed if the order status is "complete"
            if ($order->getStatus() !== 'complete') {
                return $this;
            }

            $objectManager = ObjectManager::getInstance();
            $connection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
            $incrementId = $order->getIncrementId();

            // Update the transaction status to "complete" in lof_rewardpoints_transaction
            $connection->update(
                'lof_rewardpoints_transaction',
                [
                    'status' => 'complete',
                    'is_applied' => 1,
                    'apply_at' => new \Zend_Db_Expr('NOW()')
                ],
                [
                    'order_id = ?' => $incrementId,
                    'action = ?' => 'earning_order',
                    'status = ?' => 'pending'
                ]
            );

            // Also update the points_status in subtotal_rewardpoints to indicate points are awarded
            $connection->update(
                'subtotal_rewardpoints',
                ['points_status' => 1],
                ['status' => 'complete'],
                ['order_id = ?' => $incrementId]
            );

            // Log the update
            $this->logger->info('Updated reward points status to complete for order #' . $incrementId);

        } catch (\Exception $e) {
            $this->logger->error('Error updating reward points status: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }

        return $this;
    }
}
