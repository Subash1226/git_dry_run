<?php
namespace Lof\Subtotal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Lof\Subtotal\Helper\Data;

class CreatePoints implements ObserverInterface
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

            $objectManager = ObjectManager::getInstance();
            $connection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
            $subtotal = $order->getSubtotal();
            $points = floor($subtotal);
            $incrementId = $order->getIncrementId();
            $connection->insert(
                'subtotal_rewardpoints',
                [
                    'customer_id' => $order->getCustomerId(),
                    'order_id' => $incrementId,
                    'order_status' => $order->getStatus(),
                    'points_status' => 0,
                    'points' => $points
                ]
            );

            if ($points > 0) {
                $transactionCode = 'REWARD-SUB-' . strtoupper(substr(uniqid(), -8));
                $paramsArray = [
                    'earning_rate' => ['points' => $points],
                    'spending_rate' => ['discount' => 0],
                    'order_subtotal' => $subtotal
                ];

                $params = json_encode($paramsArray);
                $storeId = $order->getStoreId();
                $title = "Earned {$points} points for the order #{$incrementId}";
                $expiresAt = new \DateTime();
                $expiresAt->modify('+365 days');
                $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');

                $connection->insert(
                    'lof_rewardpoints_transaction',
                    [
                        'customer_id' => $order->getCustomerId(),
                        'quote_id' => $order->getQuoteId(),
                        'amount' => $points,
                        'amount_used' => 0,
                        'title' => $title,
                        'code' => $transactionCode,
                        'action' => 'earning_order',
                        'status' => 'pending',
                        'params' => $params,
                        'is_expiration_email_sent' => 0,
                        'email_message' => null,
                        'apply_at' => null,
                        'is_applied' => 0,
                        'is_expired' => 0,
                        'expires_at' => $expiresAtFormatted,
                        'updated_at' => new \Zend_Db_Expr('NOW()'),
                        'created_at' => new \Zend_Db_Expr('NOW()'),
                        'store_id' => $storeId,
                        'order_id' => $incrementId,
                        'admin_user_id' => 0
                    ]
                );
                $purchaseParams = [
                    'earning_rate' => ['rules' => [], 'points' => $points],
                    'spending_rate' => ['rules' => [], 'discount' => 0],
                    'earning_catalog_rule' => [],
                    'earning_cart_rule' => [],
                    'earning_product_points' => ['rules' => []]
                ];

                $serializedParams = serialize($purchaseParams);
                $dateTime = new \Zend_Db_Expr('NOW()');

                $connection->insert(
                    'lof_rewardpoints_purchase',
                    [
                        'quote_id' => $order->getQuoteId(),
                        'order_id' => $incrementId,
                        'customer_id' => $order->getCustomerId(),
                        'spend_amount' => null,
                        'discount' => '0',
                        'spend_points' => 0.0000,
                        'spend_cart_points' => null,
                        'spend_catalog_points' => null,
                        'earn_points' => (float)$points,
                        'earn_catalog_points' => 0.0000,
                        'subtotal' => $subtotal,
                        'earn_cart_points' => 0.0000,
                        'params' => $serializedParams,
                        'created_at' => $dateTime,
                        'base_discount' => 0.0000
                    ]
                );

                $this->logger->info('Created transaction and purchase records for order #' . $incrementId .
                                 ': ' . $points . ' points with code ' . $transactionCode);
            }

            $this->logger->info('Created reward points for order #' . $incrementId . ': ' . $points . ' points');

        } catch (\Exception $e) {
            $this->logger->error('Error creating reward points: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }

        return $this;
    }
}
