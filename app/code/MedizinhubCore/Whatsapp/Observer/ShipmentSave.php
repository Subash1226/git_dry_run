<?php

namespace MedizinhubCore\Whatsapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MedizinhubCore\Whatsapp\Model\WhatsappService;
use MedizinhubCore\Whatsapp\Model\TemplateManager;
use MedizinhubCore\Whatsapp\Helper\Data;
use Psr\Log\LoggerInterface;

class ShipmentSave implements ObserverInterface
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
        // $this->logger->info("WhatsApp Service: Starting ShipmentSave process");
        
        $shipment = $observer->getEvent()->getShipment();
        
        if (!$shipment || !$shipment->getId()) {
            $this->logger->info('WhatsApp Observer: No shipment found or shipment has no ID');
            return;
        }

        $order = $shipment->getOrder();
        
        if (!$order || !$order->getId()) {
            $this->logger->info('WhatsApp Observer: No order found for shipment');
            return;
        }

        // $this->logger->info("WhatsApp Observer: New shipment created for order #{$order->getIncrementId()}");
        $this->sendWhatsAppShippingNotification($order, $shipment);
    }

    protected function sendWhatsAppShippingNotification($order, $shipment)
    {
        try {
            $billingAddress = $order->getBillingAddress();
            if (!$billingAddress || !$billingAddress->getTelephone()) {
                $this->logger->info('WhatsApp Observer: No billing address or phone number found');
                return;
            }
            
            // $this->logger->info("WhatsApp Service: Starting shipping notification for order #{$order->getIncrementId()}");

            $phoneNumber = $billingAddress->getTelephone();

            usleep(500000);
            $trackingNumber = $this->getTrackingInfo($order, $shipment);

            $templateParams = $this->templateManager->prepareTrackingTemplateParams($order, $trackingNumber);
            
            $this->logger->info("WhatsApp Service: Shipping templateParams : " . json_encode($templateParams));
            $templateName = $this->helper->getTrackingTemplateName();

            $this->whatsappService->sendWhatsAppMessage($phoneNumber, $templateName, $templateParams);
            
        } catch (\Exception $e) {
            // Log error but don't break the order flow
            $this->logger->error('WhatsApp shipping notification error: ' . $e->getMessage());
        }
    }

    /**
     * Get tracking information - Enhanced with multiple sources
     */
    private function getTrackingInfo($order, $currentShipment = null)
    {
        $trackingInfo = [];

        try {
            if ($currentShipment) {
                
                // Reload the shipment to get fresh data
                $currentShipment = $currentShipment->load($currentShipment->getId());
                $tracks = $currentShipment->getAllTracks();
                
                foreach ($tracks as $track) {
                    $carrierTitle = $track->getTitle() ?: ($track->getCarrierCode() ?: 'Courier');
                    $trackNumber = $track->getTrackNumber();
                    
                    if ($trackNumber && trim($trackNumber) !== '') {
                        $trackingInfo[] = "{$carrierTitle}: {$trackNumber}";
                    }
                }
            }
        } catch (\Exception $e) {
        }

        $result = !empty($trackingInfo) ? implode(', ', $trackingInfo) : 'Tracking will be updated soon';
        
        return $result;
    }
}