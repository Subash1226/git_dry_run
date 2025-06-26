<?php
namespace Lof\Subtotal\Model;

use Lof\Subtotal\Api\RewardPointsServiceInterface;
use Lof\Subtotal\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class RewardPointsService implements RewardPointsServiceInterface
{
    protected $orderRepository;
    protected $helper;
    protected $logger;
    protected $resourceConnection;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection
    ) {
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function generatePoints($orderId)
    {
        if (!$this->helper->isEnabled()) {
            return ['success' => false, 'message' => 'Reward points feature is disabled'];
        }

        try {
            $order = $this->orderRepository->get($orderId);
            if (!$order || !$order->getCustomerId()) {
                return ['success' => false, 'message' => 'Invalid order or no customer associated'];
            }

            $connection = $this->resourceConnection->getConnection();
            $subtotal = $order->getSubtotal();
            $points = floor($subtotal);
            $incrementId = $order->getIncrementId();

            // Check if points already exist for this order
            $select = $connection->select()
                ->from('subtotal_rewardpoints')
                ->where('order_id = ?', $incrementId);
            $existingRecord = $connection->fetchRow($select);

            if ($existingRecord) {
                return [
                    'success' => true,
                    'message' => 'Points already generated for this order',
                    'points' => (int)$existingRecord['points'],
                    'order_id' => $incrementId
                ];
            }

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
            }

            return [
                'success' => true,
                'message' => 'Points generated successfully',
                'points' => $points,
                'order_id' => $incrementId,
                'transaction_code' => $points > 0 ? $transactionCode : null
            ];

        } catch (NoSuchEntityException $e) {
            $this->logger->error('Order not found: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Order not found'];
        } catch (\Exception $e) {
            $this->logger->error('Error generating points: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
