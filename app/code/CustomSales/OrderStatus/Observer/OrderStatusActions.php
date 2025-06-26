<?php
namespace CustomSales\OrderStatus\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderStatusActions implements ObserverInterface
{
    protected $logger;
    protected $orderRepository;
    protected $orderManagement;

    public function __construct(
        LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('OrderStatusActions Observer started');
        
        try {
            $shipment = $observer->getEvent()->getShipment();
            if ($shipment) {
                $order = $shipment->getOrder();
                $this->logger->info('Processing shipment for order #' . $order->getIncrementId());
                $this->handleShipped($order);
                return;
            }
            
            $order = $observer->getEvent()->getOrder();
            
            if (!$order) {
                $this->logger->error('No order found in observer');
                return;
            }

            $this->logger->info('Processing order #' . $order->getIncrementId());
            
            $newStatus = $order->getStatus();
            $currentState = $order->getState();
            $originalState = $order->getOrigData('state');
            $originalStatus = $order->getOrigData('status');

            $this->logger->info('Current State: ' . $currentState);
            $this->logger->info('Original State: ' . $originalState);
            $this->logger->info('Original Status: ' . $originalStatus);
            $this->logger->info('New Status: ' . $newStatus);

            if ($originalStatus !== $newStatus) {
                switch ($newStatus) {
                    case 'under_review':
                        $this->handleUnderReview($order);
                        break;

                    case 'prescription_verified':
                        $this->handlePrescriptionVerified($order);
                        break;  

                    case 'holded':
                        $this->handleHold($order);
                        break;

                    case 'processing':
                        if ($originalState === Order::STATE_HOLDED) {
                            $this->handleUnhold($order);
                        }
                        break;

                    case 'canceled':
                        $this->handleCancel($order);
                        break;
                }
            }

            $this->logger->info('Final state: ' . $order->getState());
            $this->logger->info('Final status: ' . $order->getStatus());
            
        } catch (\Exception $e) {
            $this->logger->error('Error in OrderStatusActions: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    protected function handleUnderReview(Order $order)
    {
        try {
            if ($order->getState() !== Order::STATE_COMPLETE) {
                $order->setState(Order::STATE_PROCESSING)
                      ->setStatus('under_review');
                $this->logger->info('Order set to Under Review');
            } else {
                $this->logger->warning('Cannot set completed order to Under Review');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error setting Under Review: ' . $e->getMessage());
        }
    }

    protected function handleHold(Order $order)
    {
        try {
            if ($order->canHold()) {
                $this->orderManagement->hold($order->getId());
                
                $order->setState(Order::STATE_HOLDED)
                      ->setStatus('holded');
                
                $this->orderRepository->save($order);
                
                $this->logger->info('Order placed on hold successfully');
            } else {
                $this->logger->warning('Cannot hold order #' . $order->getIncrementId());
            }
        } catch (\Exception $e) {
            $this->logger->error('Error holding order: ' . $e->getMessage());
        }
    }

    protected function handleUnhold(Order $order)
    {
        try {
            if ($order->canUnhold()) {
                $this->orderManagement->unhold($order->getId());
                
                $order->setState(Order::STATE_PROCESSING)
                      ->setStatus('processing');
                
                $this->orderRepository->save($order);
                
                $this->logger->info('Order removed from hold successfully');
            } else {
                $this->logger->warning('Cannot unhold order #' . $order->getIncrementId());
            }
        } catch (\Exception $e) {
            $this->logger->error('Error unholding order: ' . $e->getMessage());
        }
    }

    protected function handleCancel(Order $order)
    {
        try {
            if ($order->canCancel()) {
                $this->orderManagement->cancel($order->getId());
                
                $this->orderRepository->save($order);
                
                $this->logger->info('Order cancelled successfully');
            } else {
                $this->logger->warning('Cannot cancel order #' . $order->getIncrementId());
            }
        } catch (\Exception $e) {
            $this->logger->error('Error cancelling order: ' . $e->getMessage());
        }
    }

    protected function handleShipped(Order $order)
    {
        try {
            if ($order->getState() === Order::STATE_PROCESSING) {
                $order->setState(Order::STATE_PROCESSING)
                      ->setStatus('order_shipped');
                
                $this->orderRepository->save($order);
                
                $this->logger->info('Order status changed to shipped for order #' . $order->getIncrementId());
            } else {
                $this->logger->warning('Cannot change status to shipped for order #' . $order->getIncrementId());
            }
        } catch (\Exception $e) {
            $this->logger->error('Error setting order shipped status: ' . $e->getMessage());
        }
    }

    protected function handlePrescriptionVerified(Order $order)
    {
        try {
            if ($order->getState() !== Order::STATE_COMPLETE) {
                $order->setState(Order::STATE_PROCESSING)
                      ->setStatus('prescription_verified');
                $this->logger->info('Order set to Prescription Verified');
            } else {
                $this->logger->warning('Cannot set completed order to Prescription Verified');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error setting Prescription Verified: ' . $e->getMessage());
        }
    }
}