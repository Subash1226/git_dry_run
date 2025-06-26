<?php

namespace MedizinhubCore\Whatsapp\Model;

use MedizinhubCore\Whatsapp\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class WhatsappService
{
    protected $helper;
    protected $curl;
    protected $logger;
    protected $json;

    public function __construct(
        Data $helper,
        Curl $curl,
        LoggerInterface $logger,
        Json $json
    ) {
        $this->helper = $helper;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->json = $json;
    }

    public function sendWhatsAppMessage($phoneNumber, $templateName, $parameters)
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        try {
            // $this->logger->info('WhatsApp API Response: WhatsappService');
            $apiKey = $this->helper->getApiKey();
            $templateName = $templateName;

            $apiUrl = 'https://backend.aisensy.com/campaign/t1/api/v2';

            $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

            $data = [
                'apiKey' => $apiKey,
                'campaignName' => $templateName,
                'destination' => $phoneNumber,
                'userName' => $parameters[1],
                'templateParams' => $parameters
            ];

            // $this->logger->info('WhatsApp API Data: ' . json_encode($data));

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                throw new \Exception('Failed to send WhatsApp message. Response: ' . $response);
            }

            curl_close($ch);
            $this->logger->info('WhatsApp API Response: ' . $response);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('WhatsApp API Error: ' . $e->getMessage());
            return false;
        }
    }


    private function cleanPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present (assuming India +91)
        if (strlen($phoneNumber) == 10) {
            $phoneNumber = '91' . $phoneNumber;
        }
        
        return $phoneNumber;
    }
}