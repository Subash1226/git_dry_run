<?php

namespace SalesOrder\UserLog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class LogOrderViewController implements ObserverInterface
{
    private $orderViewLogFactory;
    private $authSession;
    private $dateTime;
    private $request;
    private $logger;

    public function __construct(
        \SalesOrder\UserLog\Model\OrderViewLogFactory $orderViewLogFactory,
        AuthSession $authSession,
        DateTime $dateTime,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->orderViewLogFactory = $orderViewLogFactory;
        $this->authSession = $authSession;
        $this->dateTime = $dateTime;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        static $logged = false;
        if ($logged) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $user = $this->authSession->getUser();

        if ($order && $user) {
            $activityType = $this->determineActivityType($order);
            
            if ($activityType) {
                $orderViewLog = $this->orderViewLogFactory->create();
                $orderViewLog->setOrderId($order->getId());
                $orderViewLog->setUserId($user->getId());
                $orderViewLog->setUsername($user->getUsername());
                $orderViewLog->setActivityType($activityType);
                $orderViewLog->save();
            }
        }
        $logged = true;
    }

    private function determineActivityType(Order $order)
    {
        $actionName = $this->request->getFullActionName();
        $postData = $this->request->getPost();
        $this->logger->info('Action Name: ' . $actionName);

        switch ($actionName) {
            case 'sales_order_create_reorder':
                    return 'reorder_completed';
                break;
            case 'adminhtml_order_shipment_save':
                    return 'shipment_completed';
                break;
            case 'sales_order_invoice_save':
                    return 'invoice_completed';
                break;
            case 'sales_order_addComment':
                    return 'comment_added';
                break;
            case 'sales_order_hold':
                return 'order_held';
            case 'sales_order_unhold':
                return 'order_unheld';
            case 'sales_order_email':
                return 'email_sent';
            case 'sales_order_cancel':
                return 'order_cancelled';
        }

        return null;
    }
}