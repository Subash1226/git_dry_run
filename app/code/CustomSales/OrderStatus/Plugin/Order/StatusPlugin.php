<?php
namespace CustomSales\OrderStatus\Plugin\Order;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class StatusPlugin
{
    protected $request;
    protected $orderRepository;
    protected $logger;
    protected $orderConfig;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Config $orderConfig,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->orderConfig = $orderConfig;
        $this->logger = $logger;
    }

    public function afterGetStatuses(\Magento\Sales\Model\Order\Config $subject, array $result)
    {
        $result['under_review'] = __('Under Review');
        $result['prescription_verified'] = __('Prescription Verified');

        return $result;
    }

    public function afterGetStateStatuses(\Magento\Sales\Model\Order\Config $subject, array $result)
    {
        try {

            $orderId = $this->request->getParam('order_id');
            if (!$orderId) {
                return $result;
            }

            $order = $this->orderRepository->get($orderId);
            $state = $order->getState();
            $status = $order->getStatus();
    
            $statusFlow = [
                'under_review',
                'prescription_verified',
                'order_shipped',
                'complete',
                'closed',
                'canceled'
            ];
            $currentStatusPosition = array_search($status, $statusFlow);
            
            if ($currentStatusPosition === false && in_array($state, [Order::STATE_PROCESSING, Order::STATE_NEW])) {
                $result['under_review'] = __('Under Review');
            }
    
            if ($status === 'under_review') {
                $result['prescription_verified'] = __('Prescription Verified');
            }

            if ($state !== Order::STATE_COMPLETE && $order->canHold()) {
                $result['holded'] = __('Hold');
            }

            if ($state === Order::STATE_HOLDED && $order->canUnhold()) {
                $result['processing'] = __('Unhold');
            }

            if ($order->canCancel()) {
                $result['canceled'] = __('Cancel');
            }

            $this->logger->info('Available statuses for order #' . $orderId . ': ' . print_r($result, true));

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error in StatusPlugin: ' . $e->getMessage());
            return $result;
        }
    }
}
