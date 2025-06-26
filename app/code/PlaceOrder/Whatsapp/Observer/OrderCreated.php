<?php
namespace PlaceOrder\Whatsapp\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use PlaceOrder\Whatsapp\Helper\Data as WhatsappHelper;

class OrderCreated implements ObserverInterface
{
    protected $logger;
    protected $whatsappHelper;

    public function __construct(
        LoggerInterface $logger,
        WhatsappHelper $whatsappHelper
    ) {
        $this->logger = $logger;
        $this->whatsappHelper = $whatsappHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            if (!$this->whatsappHelper->isEnabled()) {
                return;
            }

            $order = $observer->getEvent()->getOrder();
            $shippingAddress = $order->getShippingAddress();

            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            
            // Get mobile number from shipping address, fallback to billing if not available
            $mobileNumber = 'No phone number';
            if ($shippingAddress && $shippingAddress->getTelephone()) {
                $mobileNumber = $shippingAddress->getTelephone();
            } elseif ($order->getBillingAddress() && $order->getBillingAddress()->getTelephone()) {
                $mobileNumber = $order->getBillingAddress()->getTelephone();
            }
            
            $shippingStreet = $shippingAddress ? implode(' ', $shippingAddress->getStreet()) : 'No street address';
            $shippingCity = $shippingAddress ? $shippingAddress->getCity() : 'No city';
            $shippingPostcode = $shippingAddress ? $shippingAddress->getPostcode() : 'No postcode';
            $shippingCountry = $shippingAddress ? $shippingAddress->getCountryId() : 'No country';
            $shippingAddressFormatted = "$shippingStreet, $shippingCity, $shippingPostcode, $shippingCountry";
            
            $paymentMethod = $this->getPaymentMethodLabel($order->getPayment()->getMethod());
            
            $orderStatus = $order->getStatus();
            $orderStatusLabel = $order->getStatusLabel() ?: ucfirst(str_replace('_', ' ', $orderStatus));
            
            // Get ordered products
            $orderedItems = [];
            foreach ($order->getAllItems() as $item) {
                $orderedItems[] = $item->getName() . ' (Qty: ' . (int)$item->getQtyOrdered() . ')';
            }
            $productsOrdered = implode(', ', $orderedItems);

            // Send to all staff members
            $staffNumbers = $this->whatsappHelper->getStaffNumbers();
            foreach ($staffNumbers as $staffNumber) {
                $payload = [
                    'apiKey' => $this->whatsappHelper->getApiKey(),
                    'campaignName' => $this->whatsappHelper->getCampaignName(),
                    'destination' => trim($staffNumber),
                    'userName' => 'Staff',
                    'templateParams' => [
                        $customerName,
                        $order->getIncrementId(),
                        $mobileNumber,
                        $shippingAddressFormatted,
                        $productsOrdered,
                        number_format($order->getGrandTotal(), 2),
                        $paymentMethod,
                        $orderStatusLabel
                    ]
                ];

                $this->sendWhatsAppMessage($payload);
            }

            $this->logger->info('WhatsApp notifications sent for new order to staffs:', [
                'order_id' => $order->getIncrementId(),
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'order_status' => $orderStatusLabel
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error sending WhatsApp notification staffs: ' . $e->getMessage());
        }
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