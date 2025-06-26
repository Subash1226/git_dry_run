<?php
namespace MedizinhubCore\Whatsapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MedizinhubCore\Whatsapp\Model\WhatsappService;
use MedizinhubCore\Whatsapp\Model\TemplateManager;
use MedizinhubCore\Whatsapp\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

class CodOrderPlaced implements ObserverInterface
{
    protected $whatsappService;
    protected $templateManager;
    protected $helper;
    protected $checkoutSession;
    protected $orderFactory;
    protected $logger;

    public function __construct(
        WhatsappService $whatsappService,
        TemplateManager $templateManager,
        Data $helper,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        LoggerInterface $logger
    ) {
        $this->whatsappService = $whatsappService;
        $this->templateManager = $templateManager;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            // $this->logger->info("WhatsApp Service: Starting COD OrderPlaced process");
            
            // Get the last order from checkout session
            $lastOrderId = $this->checkoutSession->getLastOrderId();
            
            if (!$lastOrderId) {
                $this->logger->info("WhatsApp Service: No last order ID found in checkout session");
                return;
            }

            // Load the order
            $order = $this->orderFactory->create()->load($lastOrderId);
            
            if (!$order || !$order->getId()) {
                $this->logger->info("WhatsApp Service: Could not load order with ID: " . $lastOrderId);
                return;
            }

            // Get payment method
            $paymentMethod = $order->getPayment()->getMethod();
            
            // $this->logger->info("WhatsApp Service: Order ID: " . $order->getId());
            // $this->logger->info("WhatsApp Service: Order Increment ID: " . $order->getIncrementId());
            // $this->logger->info("WhatsApp Service: Payment Method: " . $paymentMethod);
            
            // Only send WhatsApp notification for COD orders
            if ($paymentMethod !== 'cashondelivery') {
                // $this->logger->info("WhatsApp Service: Skipping notification - not a COD order (Method: " . $paymentMethod . ")");
                return;
            }

            // $this->logger->info("WhatsApp Service: COD order confirmed, proceeding with WhatsApp notification");
            $this->sendWhatsAppNotification($order);
           
        } catch (\Exception $e) {
            // Log error but don't break the checkout flow
            $this->logger->error('WhatsApp COD notification error: ' . $e->getMessage());
            $this->logger->error('WhatsApp COD notification error trace: ' . $e->getTraceAsString());
        }
    }

    protected function sendWhatsAppNotification($order)
    {
        try {
            $billingAddress = $order->getBillingAddress();
            
            // $this->logger->info("WhatsApp Service: Checking billing address for COD order");
            
            if (!$billingAddress || !$billingAddress->getTelephone()) {
                $this->logger->info("WhatsApp Service: No billing address or phone number found for COD order");
                return;
            }

            $phoneNumber = $billingAddress->getTelephone();
            // $this->logger->info("WhatsApp Service: Phone number found: " . $phoneNumber);

            // Use the same template as the original order notification
            $templateName = $this->helper->getOrderTemplateName();
            $templateParams = $this->templateManager->prepareOrderTemplateParams($order);
            
            // $this->logger->info("WhatsApp Service: COD Template name: " . $templateName);
            $this->logger->info("WhatsApp Service: COD Template params: " . json_encode($templateParams));
            
            // Send the WhatsApp message
            $result = $this->whatsappService->sendWhatsAppMessage($phoneNumber, $templateName, $templateParams);
            
            // $this->logger->info("WhatsApp Service: COD notification sent successfully for order " . $order->getIncrementId());
            
        } catch (\Exception $e) {
            $this->logger->error('WhatsApp COD sendWhatsAppNotification error: ' . $e->getMessage());
            throw $e;
        }
    }
}