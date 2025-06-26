<?php

namespace MedizinhubCore\Whatsapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MedizinhubCore\Whatsapp\Model\WhatsappService;
use MedizinhubCore\Whatsapp\Model\TemplateManager;
use MedizinhubCore\Whatsapp\Helper\Data;
use Psr\Log\LoggerInterface;

class OrderStatusChange implements ObserverInterface
{
    protected $whatsappService;
    protected $templateManager;
    protected $helper;
    protected $logger;

    public function __construct(
        WhatsappService $whatsappService,
        TemplateManager $templateManager,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->whatsappService = $whatsappService;
        $this->templateManager = $templateManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        // $this->logger->info("WhatsApp Service: Starting OrderStatusChange process");
        $order = $observer->getEvent()->getOrder();
        
        if (!$order || !$order->getId()) {
            return;
        }
        
        // Skip if this is a new order (will be handled by OrderPlaced observer)
        if ($order->isObjectNew() || !$order->getOrigData()) {
            // $this->logger->info('WhatsApp Observer: Skipping new order, will be handled by OrderPlaced observer');
            return;
        }
        
        if (!$order->dataHasChangedFor('status')) {
            // $this->logger->info('WhatsApp Observer: Order status has not changed, skipping notification');
            return;
        }

        // Check if status actually changed
        $originalStatus = $order->getOrigData('status');
        $currentStatus = $order->getStatus();
        
        if ($originalStatus == $currentStatus) {
            // $this->logger->info('WhatsApp Observer: Status values are the same, skipping');
            return;
        }

        // Skip if changing from null/empty to pending (new order scenario)
        if (empty($originalStatus) && in_array($currentStatus, ['pending', 'new'])) {
            // $this->logger->info('WhatsApp Observer: Skipping new order status change from empty to pending/new');
            return;
        }

        // Skip shipping-related status changes (handled by ShipmentSave observer)
        if (in_array($currentStatus, ['shipped', 'order_shipped', 'processing_shipping', 'ready_to_ship'])) {
            // $this->logger->info("WhatsApp Observer: Skipping shipping status '{$currentStatus}' - handled by ShipmentSave observer");
            return;
        }

        // $this->logger->info("WhatsApp Observer: Status changed from '{$originalStatus}' to '{$currentStatus}'");
        $this->sendWhatsAppNotification($order);
    }

    protected function sendWhatsAppNotification($order)
    {
        try {
            $billingAddress = $order->getBillingAddress();
            if (!$billingAddress || !$billingAddress->getTelephone()) {
                $this->logger->info('WhatsApp Observer: No billing address or phone number found');
                return;
            }
            
            // $this->logger->info("WhatsApp Service: Starting OrderStatusChange sendWhatsAppNotification");

            $phoneNumber = $billingAddress->getTelephone();
            $templateName = $this->helper->getOrderTemplateName();
            
            // Use general order template for non-shipping status changes
            $templateParams = $this->templateManager->prepareOrderTemplateParams($order);
            
            $this->logger->info("WhatsApp Service: OrderStatusChange templateParams : " . json_encode($templateParams));

            $this->whatsappService->sendWhatsAppMessage($phoneNumber, $templateName, $templateParams);
            
        } catch (\Exception $e) {
            // Log error but don't break the order flow
            $this->logger->error('WhatsApp notification error: ' . $e->getMessage());
        }
    }
}