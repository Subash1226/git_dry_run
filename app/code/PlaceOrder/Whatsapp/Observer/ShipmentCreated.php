<?php
namespace PlaceOrder\Whatsapp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\ResourceConnection;
use PlaceOrder\Whatsapp\Helper\Data as WhatsappHelper;

class ShipmentCreated implements ObserverInterface
{
    protected $logger;
    protected $curlClient;
    protected $timezone;
    protected $resourceConnection;
    protected $whatsappHelper;

    public function __construct(
        LoggerInterface $logger,
        Curl $curlClient,
        TimezoneInterface $timezone,
        WhatsappHelper $whatsappHelper,
        ResourceConnection $resourceConnection
    ) {
        $this->logger = $logger;
        $this->curlClient = $curlClient;
        $this->timezone = $timezone;
        $this->whatsappHelper = $whatsappHelper;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        try {
            if (!$this->whatsappHelper->isEnabled()) {
                return;
            }

            // Get shipment from the event
            $shipment = $observer->getEvent()->getShipment();
            if (!$shipment) {
                $this->logger->error('No shipment found in observer event');
                return;
            }

            // Get order from shipment
            $order = $shipment->getOrder();
            if (!$order) {
                $this->logger->error('No order found for shipment: ' . $shipment->getIncrementId());
                return;
            }

            // Get order status information
            $originalData = $order->getOrigData();
            $oldStatus = isset($originalData['status']) ? $originalData['status'] : $order->getStatus();
            $newStatus = 'order_shipped';

            // Small delay to allow tracking to be saved if it's part of the same transaction
            usleep(500000); // 0.5 second delay
            
            $this->sendShipmentNotification($order, $shipment, $oldStatus, $newStatus);

        } catch (\Exception $e) {
            $this->logger->error('Error in ShipmentCreated observer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send shipment notification
     */
    private function sendShipmentNotification($order, $shipment, $oldStatus, $newStatus)
    {
        try {
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
            $mobileNumber = $this->getMobileNumber($order, $billingAddress, $shippingAddress);

            if (!$mobileNumber || strlen(preg_replace('/[^\d]/', '', $mobileNumber)) < 10) {
                $this->logger->error('Invalid or missing mobile number for order:', [
                    'order_id' => $order->getIncrementId(),
                    'mobile_number' => $mobileNumber
                ]);
                return;
            }

            $mobileNumber = $this->formatPhoneNumber($mobileNumber);
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname() ?: 'Valued Customer';
            $firstName = $order->getCustomerFirstname() ?: 'Valued Customer';
            $currentStatusLabel = $this->getStatusLabel($newStatus);
            $previousStatusLabel = $this->getStatusLabel($oldStatus);
            
            // Get tracking info with multiple methods
            $trackingInfo = $this->getTrackingInfo($order, $shipment);
            $hasTracking = $trackingInfo !== 'Tracking will be updated soon';
            $grandTotal = $order->getOrderCurrency()->formatTxt($order->getGrandTotal());
            $deliveryAddress = $this->formatAddress($shippingAddress);
            $paymentMethod = $this->getPaymentMethodLabel($order->getPayment()->getMethod());

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
                        $previousStatusLabel,
                        $currentStatusLabel,
                        $paymentMethod,
                        number_format($order->getGrandTotal(), 2),
                        $trackingInfo
                    ]
                ];

                $this->sendWhatsAppMessage($payload);
            }
            
            $maskedMobileNumber = substr($mobileNumber, 0, 3) . '****' . substr($mobileNumber, -4);
            $this->logger->info('Enhanced order status update sent via WhatsApp for staff :', [
                'order_id' => $order->getIncrementId(),
                'shipment_id' => $shipment->getIncrementId(),
                'mobile_number' => $maskedMobileNumber,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'has_tracking' => $hasTracking,
                'tracking_info' => $trackingInfo
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error sending WhatsApp shipment notification: ' . $e->getMessage(), [
                'order_id' => $order->getIncrementId(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get mobile number with better fallback logic
     */
    private function getMobileNumber($order, $billingAddress, $shippingAddress)
    {
        // Try shipping address first (delivery contact)
        if ($shippingAddress && $shippingAddress->getTelephone()) {
            return $shippingAddress->getTelephone();
        }
        
        // Then billing address
        if ($billingAddress && $billingAddress->getTelephone()) {
            return $billingAddress->getTelephone();
        }
        
        return null;
    }

    /**
     * Format phone number
     */
    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Add 91 for Indian numbers if not present
        if (strlen($phone) == 10 && is_numeric($phone)) {
            return '91' . $phone;
        }
        
        // Remove + if present and ensure proper format
        $phone = ltrim($phone, '+');
        
        return $phone;
    }

    /**
     * Get human-readable status label
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
     * Get tracking information - Enhanced with multiple sources
     */
    private function getTrackingInfo($order, $currentShipment = null)
    {
        $trackingInfo = [];

        try {
            if ($currentShipment) {
                // $this->logger->info('Checking current shipment for tracking', [
                //     'shipment_id' => $currentShipment->getIncrementId()
                // ]);
                
                // Reload the shipment to get fresh data
                $currentShipment = $currentShipment->load($currentShipment->getId());
                $tracks = $currentShipment->getAllTracks();
                // $this->logger->info('Current shipment tracks count: ' . count($tracks));
                
                foreach ($tracks as $track) {
                    $carrierTitle = $track->getTitle() ?: ($track->getCarrierCode() ?: 'Courier');
                    $trackNumber = $track->getTrackNumber();
                    
                    if ($trackNumber && trim($trackNumber) !== '') {
                        $trackingInfo[] = "{$carrierTitle}: {$trackNumber}";
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting tracking info: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        $result = !empty($trackingInfo) ? implode(', ', $trackingInfo) : 'Tracking will be updated soon';
        
        // $this->logger->info('Final tracking info result', [
        //     'order_id' => $order->getIncrementId(),
        //     'tracking_info' => $result,
        //     'tracking_count' => count($trackingInfo)
        // ]);
        
        return $result;
    }

    /**
     * Format address for display
     */
    private function formatAddress($address)
    {
        if (!$address) {
            return 'Address not available';
        }
        
        $parts = [];
        
        if ($address->getStreet()) {
            $parts[] = implode(' ', $address->getStreet());
        }
        
        if ($address->getCity()) {
            $parts[] = $address->getCity();
        }
        
        if ($address->getPostcode()) {
            $parts[] = $address->getPostcode();
        }
        
        return implode(', ', array_filter($parts));
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