<?php

namespace MedizinhubCore\Whatsapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MedizinhubCore\Whatsapp\Model\WhatsappService;
use MedizinhubCore\Whatsapp\Model\TemplateManager;
use MedizinhubCore\Whatsapp\Helper\Data;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

class OrderPlaced implements ObserverInterface
{
    protected $whatsappService;
    protected $templateManager;
    protected $helper;
    protected $checkoutSession;
    protected $logger;

    public function __construct(
        WhatsappService $whatsappService,
        TemplateManager $templateManager,
        Data $helper,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->whatsappService = $whatsappService;
        $this->templateManager = $templateManager;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->logger->info("WhatsApp Service: Starting OrderPlaced process");
            $order = $observer->getEvent()->getOrder();
            
            if (!$order || !$order->getId()) {
                $this->logger->info("WhatsApp Service: o valid orders");
                return;
            }

            $paymentMethod = $order->getPayment()->getMethod();
            if ($paymentMethod == 'cashondelivery') {
                $this->logger->info("WhatsApp Service: Skipping notification - not a Online order (Method: " . $paymentMethod . ")");
                return;
            }

            $this->sendWhatsAppNotification($order);
            
        } catch (\Exception $e) {
            // Log error but don't break the checkout flow
            error_log('WhatsApp notification error: ' . $e->getMessage());
        }
    }

    protected function sendWhatsAppNotification($order)
    {
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress || !$billingAddress->getTelephone()) {
            return;
        }
        $this->logger->info("WhatsApp Service: Starting OrderPlaced sendWhatsAppNotification");

        $phoneNumber = $billingAddress->getTelephone();
        $templateName = $this->helper->getOrderTemplateName();
        $templateParams = $this->templateManager->prepareOrderTemplateParams($order);
        $this->logger->info("WhatsApp Service: Starting OrderPlaced templateParams : " . json_encode($templateParams));

        $this->whatsappService->sendWhatsAppMessage($phoneNumber, $templateName, $templateParams);
    }
}
