<?php
namespace PlaceOrder\Whatsapp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use PlaceOrder\Whatsapp\Helper\Data as WhatsappHelper;
use Magento\Framework\Registry;

class OrderStatusChanged implements ObserverInterface
{
    protected $logger;
    protected $whatsappHelper;
    protected $registry;

    public function __construct(
        LoggerInterface $logger,
        WhatsappHelper $whatsappHelper,
        Registry $registry
    ) {
        $this->logger = $logger;
        $this->whatsappHelper = $whatsappHelper;
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        try {
            if (!$this->whatsappHelper->isEnabled()) {
                return;
            }

            $order = $observer->getEvent()->getOrder();
            
            // Skip if this is a new order (to avoid duplicate notifications)
            if (!$order->getId() || $order->isObjectNew()) {
                return;
            }

            // Get original data to compare status change
            $originalOrder = $order->getOrigData();
            $currentStatus = $order->getStatus();
            $originalStatus = isset($originalOrder['status']) ? $originalOrder['status'] : null;
            
            // Skip notification if this is a shipment-related status change
            if ($this->isShipmentRelatedStatusChange($currentStatus)) {
                // $this->logger->info('Skipping order status notification for shipment-related change:', [
                //     'order_id' => $order->getIncrementId(),
                //     'old_status' => $originalStatus,
                //     'new_status' => $currentStatus,
                //     'reason' => 'Handled by shipment observer'
                // ]);
                return;
            }

            // Only proceed if status actually changed
            if ($originalStatus === $currentStatus || empty($originalStatus)) {
                return;
            }

            // Skip certain statuses that don't need notifications
            $skipStatuses = ['pending', 'pending_payment'];
            if (in_array($currentStatus, $skipStatuses)) {
                return;
            }

            $shippingAddress = $order->getShippingAddress();
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            
            // Get mobile number from shipping address, fallback to billing if not available
            $mobileNumber = 'No phone number';
            if ($shippingAddress && $shippingAddress->getTelephone()) {
                $mobileNumber = $shippingAddress->getTelephone();
            } elseif ($order->getBillingAddress() && $order->getBillingAddress()->getTelephone()) {
                $mobileNumber = $order->getBillingAddress()->getTelephone();
            }
            
            $paymentMethod = $this->getPaymentMethodLabel($order->getPayment()->getMethod());
            $currentStatusLabel = $order->getStatusLabel() ?: ucfirst(str_replace('_', ' ', $currentStatus));
            $originalStatusLabel = $this->getStatusLabel($originalStatus);
            $trackingInfo = $this->getTrackingInfo($order);

            // Send to all staff members
            $staffNumbers = $this->whatsappHelper->getStaffNumbers();
            foreach ($staffNumbers as $staffNumber) {
                $payload = [
                    'apiKey' => $this->whatsappHelper->getApiKey(),
                    'campaignName' => $this->whatsappHelper->getStatusChangeCampaignName(),
                    'destination' => trim($staffNumber),
                    'userName' => 'Staff',
                    'templateParams' => [
                        $customerName,
                        $order->getIncrementId(),
                        $mobileNumber,
                        $originalStatusLabel,
                        $currentStatusLabel,
                        $paymentMethod,
                        number_format($order->getGrandTotal(), 2),
                        $trackingInfo
                    ]
                ];

                $this->sendWhatsAppMessage($payload);
            }

            $this->logger->info('WhatsApp status change notifications sent to staff:', [
                'order_id' => $order->getIncrementId(),
                'customer_name' => $customerName,
                'status_from' => $originalStatus,
                'status_to' => $currentStatus
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error sending WhatsApp status change notification: ' . $e->getMessage());
        }
    }

    /**
     * Check if the status change is related to shipment creation
     */
    private function isShipmentRelatedStatusChange($newStatus)
    {
        // Define shipment-related status transitions
        $shipmentRelatedStatuses = ['shipped', 'ready_to_ship', 'out_for_delivery', 'order_shipped'];
        
        // Skip if changing TO a shipment-related status
        if (in_array($newStatus, $shipmentRelatedStatuses)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get human-readable payment method label
     *
     * @param string $paymentMethodCode
     * @return string
     */
    private function getPaymentMethodLabel($paymentMethodCode)
    {
        $paymentMethods = [
            'cashondelivery' => 'Cash on Delivery (COD)',
            'cod' => 'Cash on Delivery (COD)',
            'razorpay' => 'Razorpay (Online Payment)',
            'razorpay_cc' => 'Razorpay Credit Card',
            'razorpay_dc' => 'Razorpay Debit Card',
            'razorpay_nb' => 'Razorpay Net Banking',
            'razorpay_wallet' => 'Razorpay Wallet',
            'razorpay_upi' => 'Razorpay UPI',
            'checkmo' => 'Check/Money Order',
            'banktransfer' => 'Bank Transfer',
            'purchaseorder' => 'Purchase Order',
            'free' => 'Free Payment',
            'paypal_express' => 'PayPal Express',
            'paypal_standard' => 'PayPal Standard',
            'stripe' => 'Stripe Payment',
            'authorizenet_directpost' => 'Authorize.Net'
        ];

        return isset($paymentMethods[$paymentMethodCode]) 
            ? $paymentMethods[$paymentMethodCode] 
            : ucfirst(str_replace('_', ' ', $paymentMethodCode));
    }

    /**
     * Get human-readable status label
     *
     * @param string $statusCode
     * @return string
     */
    private function getStatusLabel($statusCode)
    {
        $statusLabels = [
            'pending' => 'Order Placed',
            'pending_payment' => 'Pending Payment',
            'processing' => 'Processing',
            'under_review' => 'Under review',
            'prescription_verified' => 'Prescription Verified',
            'order_shipped' => 'Shipped',
            'shipped' => 'Shipped',
            'complete' => 'Delivered',
            'closed' => 'Completed',
            'canceled' => 'Cancelled',
            'holded' => 'On Hold',
            'payment_review' => 'Payment Under Review',
            'fraud' => 'Under Review',
            'pending_cod' => 'Pending COD Verification',
            'ready_to_ship' => 'Ready to Ship',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered'
        ];

        return isset($statusLabels[$statusCode]) 
            ? $statusLabels[$statusCode] 
            : ucfirst(str_replace('_', ' ', $statusCode));
    }

    /**
     * Get tracking information for the order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getTrackingInfo($order)
    {
        $trackingInfo = [];

        $shipmentCollection = $order->getShipmentsCollection();
        foreach ($shipmentCollection as $shipment) {
            $tracks = $shipment->getAllTracks();
            foreach ($tracks as $track) {
                $carrierTitle = $track->getCarrierCode() === 'custom' ? '' : $track->getTitle();
                $title = $track->getTitle(); // Your input, e.g., "Professional"
                $trackNumber = $track->getTrackNumber(); // e.g., "12345678"

                $trackingInfo[] = "{$carrierTitle} - {$title}: {$trackNumber}";
            }
        }

        return !empty($trackingInfo) ? implode(', ', $trackingInfo) : 'No tracking available';
    }


    private function sendWhatsAppMessage(array $payload)
    {
        $url = 'https://backend.aisensy.com/campaign/t1/api/v2';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to send WhatsApp message. Response: ' . $response);
        }

        curl_close($ch);
    }
}