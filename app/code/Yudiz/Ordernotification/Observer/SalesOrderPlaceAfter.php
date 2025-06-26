<?php
namespace Yudiz\Ordernotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\State;

class SalesOrderPlaceAfter implements ObserverInterface
{
    protected $notifier;
    protected $urlBuilder;
    protected $backendUrl;
    protected $appState;

    public function __construct(
        NotifierInterface $notifier,
        UrlInterface $urlBuilder,
        BackendUrlInterface $backendUrl,
        State $appState
    ) {
        $this->notifier = $notifier;
        $this->urlBuilder = $urlBuilder;
        $this->backendUrl = $backendUrl;
        $this->appState = $appState;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $orderId = $order->getIncrementId();
            $customerName = $order->getCustomerName() ?: 'Guest'; // Fallback for guest orders

            // Determine if the order was placed from admin or frontend
            $isAdminOrder = $this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

            $description = "New order #$orderId placed by $customerName" . ($isAdminOrder ? " (Admin Order)" : "");

            // Add a major notification in the admin panel
            $this->notifier->addMajor(
                "New Order Notification",
                $description
            );
        }
    }
}