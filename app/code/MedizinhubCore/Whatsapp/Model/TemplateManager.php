<?php

namespace MedizinhubCore\Whatsapp\Model;

use Magento\Sales\Model\Order;
use Magento\Directory\Model\CountryFactory;

class TemplateManager
{
    protected $countryFactory;

    public function __construct(
        CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
    }

    public function getOrderStatusMessage($status)
    {
        $messages = [
            'pending' => '📋 *Order Created*',
            'pending_payment' => '⏳ *Payment Pending*',
            'processing' => '✅ *Order Confirmed*',
            'under_review' => '🔍 *Under Review*',
            'prescription_verified' => '✅ *Prescription Verified*',
            'complete' => '🎉 *Order Completed*',
            'canceled' => '❌ *Order Canceled*',
            'closed' => '📋 *Order Closed*',
            'fraud' => '🚫 *Payment Failed*',
            'holded' => '⏸️ *Order On Hold*',
            'payment_review' => '💳 *Payment Under Review*',
            'shipped' => '🚚 *Order Shipped*',
            'order_shipped' => '🚚 *Order Shipped*'
        ];

        return isset($messages[$status]) ? $messages[$status] : '📋 *Order Updated*';
    }

    public function getOrderMessage($status, $orderId = null)
    {
        $messages = [
            'pending' => "Your Order (Id : {$orderId}) has been Created Successfully!. 🎉 Payment is yet to be Completed.",
            'pending_payment' => "Your Order (Id : {$orderId}) is Waiting for Payment Completion. Please Complete your Payment to Proceed.",
            'processing' => "Your Order (Id : {$orderId}) has been Confirmed and is being Processed! 🎉",
            'under_review' => "Your Order (Id : {$orderId}) is Currently Under Review by our Team. We will Update you soon!",
            'prescription_verified' => "Your Prescription has been Verified Successfully!. ✅ Your Order (Id : {$orderId}) will be Processed soon.",
            'complete' => "Your Order (Id : {$orderId}) has been Completed Successfully! . 🎉 Thank you for Choosing us!",
            'canceled' => "Your Order (Id : {$orderId}) has been Canceled due to Payment Failure or Other Reasons. Please Contact us for Assistance.",
            'shipped' => "Great News! Your Order (Id : {$orderId}) has been Shipped! 🚚",
            'order_shipped' => "Great News! Your Order (Id : {$orderId}) has been Shipped! 🚚"
        ];

        return isset($messages[$status]) ? $messages[$status] : "Your Order (Id : {$orderId}) Status has been Updated.";
    }

    public function prepareOrderTemplateParams(Order $order)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        
        $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        $currentStatus = $order->getStatus();
        
        if ($order->getPayment()->getMethod() == 'cashondelivery' && $currentStatus == 'pending') {
            $orderStatus = 'processing';
            $orderMessage = $this->getOrderMessage($orderStatus, $order->getIncrementId());
            $statusMessage = $this->getOrderStatusMessage($orderStatus);
        } else {
            $orderStatus = $currentStatus;
            $orderMessage = $this->getOrderMessage($orderStatus, $order->getIncrementId());
            $statusMessage = $this->getOrderStatusMessage($orderStatus);
        }

        $shippingAddressText = $this->formatShippingAddress($shippingAddress);
        
        // Get payment method
        $paymentMethod = $this->getPaymentMethodName($order->getPayment()->getMethod());
        
        // Get products list - Fixed to be more WhatsApp template friendly
        $productsList = $this->getProductsList($order);

        return [
            $this->cleanParameter($statusMessage),                    // {1} - Status message
            $this->cleanParameter($customerName),                     // {2} - Customer name  
            $this->cleanParameter($orderMessage),                     // {3} - Order message
            $this->cleanParameter('₹' . number_format($order->getGrandTotal(), 2)), // {4} - Amount
            $this->cleanParameter($productsList),                     // {5} - Products
            $this->cleanParameter($paymentMethod),                    // {6} - Payment method
            $this->cleanParameter($shippingAddressText),                  // {7} - Delivery address
        ];
    }

    public function prepareTrackingTemplateParams(Order $order, $trackingNumber = '')
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        
        $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        $shippingAddressText = $this->formatShippingAddress($shippingAddress);
        
        // Get payment method
        $paymentMethod = $this->getPaymentMethodName($order->getPayment()->getMethod());
        
        // Get products list - Fixed to be more WhatsApp template friendly
        $productsList = $this->getProductsList($order);
        
        return [
            ' *Order Shipped*',           // {1} - Status message
            $this->cleanParameter($customerName),                     // {2} - Customer name
            "Great news! Your Order (Id : {$order->getIncrementId()}) has been Shipped! 🚚", // {3} - Shipping message
            '₹' . $this->cleanParameter(number_format($order->getGrandTotal(), 2)), // {4} - Amount
            $this->cleanParameter($productsList),                     // {5} - Products
            $this->cleanParameter($paymentMethod),                    // {6} - Payment method
            $this->cleanParameter($shippingAddressText),                  // {7} - Delivery address
            $this->cleanParameter($trackingNumber),                   // {8} - Tracking number
            'Track Your Order Using the Tracking Number Above.', // {9} - Tracking message
        ];
    }
    
    private function formatShippingAddress($shippingAddress)
    {
        if (!$shippingAddress) {
            return 'Address not available';
        }
        
        $addressParts = [];
        
        if ($shippingAddress->getStreetLine(1)) {
            $addressParts[] = $shippingAddress->getStreetLine(1);
        }
        if ($shippingAddress->getStreetLine(2)) {
            $addressParts[] = $shippingAddress->getStreetLine(2);
        }
        if ($shippingAddress->getCity()) {
            $addressParts[] = $shippingAddress->getCity();
        }
        if ($shippingAddress->getRegion()) {
            $addressParts[] = $shippingAddress->getRegion();
        }
        if ($shippingAddress->getPostcode()) {
            $addressParts[] = $shippingAddress->getPostcode();
        }
        
        return implode(', ', $addressParts);
    }
    
    private function getPaymentMethodName($paymentMethodCode)
    {
        $paymentMethods = [
            'cashondelivery' => 'Cash on Delivery',
            'razorpay' => 'Pay Online',
            'payu' => 'PayU',
            'ccavenue' => 'CCAvenue',
            'instamojo' => 'Instamojo',
            'phonepe' => 'PhonePe',
            'paytm' => 'Paytm',
            'checkmo' => 'Check/Money Order',
            'banktransfer' => 'Bank Transfer',
            'free' => 'Free'
        ];
        
        return isset($paymentMethods[$paymentMethodCode]) ? $paymentMethods[$paymentMethodCode] : ucfirst($paymentMethodCode);
    }
    
    private function getProductsList(Order $order)
    {
        $items = [];
        $maxItems = 2; // Reduced to 2 items to keep message shorter
        $count = 0;
        
        foreach ($order->getAllVisibleItems() as $item) {
            if ($count >= $maxItems) {
                break;
            }
            
            $itemName = $item->getName();
            $qty = (int)$item->getQtyOrdered();
            $price = number_format($item->getPrice(), 2);
            
            // Clean item name and make it more template-friendly
            $itemName = $this->cleanParameter($itemName);
            
            // Simplified format without special characters that might cause issues
            $items[] = "{$itemName} (Qty: {$qty}) - Rs.{$price}";
            $count++;
        }
        
        $totalItems = count($order->getAllVisibleItems());
        if ($totalItems > $maxItems) {
            $remaining = $totalItems - $maxItems;
            $items[] = "and {$remaining} more items";
        }
        
        // Use simple separator instead of newlines
        $itemsText = implode(", ", $items);
        
        // Limit items text length more strictly
        if (strlen($itemsText) > 200) {
            $itemsText = substr($itemsText, 0, 197) . '...';
        }
        
        return $itemsText;
    }

    /**
     * Clean and validate template parameters with stricter rules
     */
    private function cleanParameter($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        // Convert to string
        $cleaned = (string)$value;
        
        // Remove line breaks and normalize whitespace
        $cleaned = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        
        // Remove problematic characters for WhatsApp templates
        // Keep only basic characters, numbers, and common punctuation
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\.,\-\(\):]/u', '', $cleaned);
        
        // Limit length to prevent parameter overflow
        if (strlen($cleaned) > 160) {
            $cleaned = substr($cleaned, 0, 157) . '...';
        }
        
        return $cleaned;
    }
}