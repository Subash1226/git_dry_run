<?php
namespace Dynamic\ConsultationFee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class QuoteToOrderObserver implements ObserverInterface
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $quote = $observer->getEvent()->getQuote();            
            $consultationFee = $quote->getData('consultation_fee');
            
            if ($consultationFee > 0) {
                $order->setDoctorConsultationFee($consultationFee);
                $order->save();
            }
        } catch (\Exception $e) {
            $this->logger->error("Error adding consultation fee to order", [
                'error' => $e->getMessage()
            ]);
        }
    }
}