<?php
namespace Dynamic\ConsultationFee\Model\Total;

use Magento\Quote\Model\Quote; 
use Magento\Quote\Model\Quote\Address\Total; 
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal; 
use Magento\Quote\Api\Data\ShippingAssignmentInterface; 
use Psr\Log\LoggerInterface;

class ConsultationFee extends AbstractTotal { 
    protected $logger;

    public function __construct(
        LoggerInterface $logger 
        ) {
            $this->logger = $logger;
        }

        /** Collect consultation fee total
         * @param Quote $quote 
         * * @param ShippingAssignmentInterface $shippingAssignment 
         * * @param Total $total 
         * * @return $this */ 
    public function collect( 
        Quote $quote, 
        ShippingAssignmentInterface $shippingAssignment,
        Total $total 
        ) {
            $this->logger->info("Starting consultation fee collection",
            [ 
                'quote_id' => $quote->getId(), 
                'initial_grand_total' => $total->getGrandTotal(), 
                'initial_subtotal' => $total->getSubtotal() 
            ]);

            parent::collect($quote, $shippingAssignment, $total); 
            $consultationFee = $quote->getData('consultation_fee');
            $isPrescriptionRequired = $quote->getIsPrescriptionRequired();

            $this->logger->info("Consultation fee data retrieved",
            [ 
                'consultation_fee' => $consultationFee, 
                'isPrescriptionRequired' => $isPrescriptionRequired, 
                'quote_id' => $quote->getId() 
            ]);

            if ($consultationFee > 0) { 
                $this->logger->info("Adding consultation fee to totals",
                [ 
                    'fee_amount' => $consultationFee, 
                    'current_grand_total' => $total->getGrandTotal(), 
                    'current_subtotal' => $total->getSubtotal() 
                ]);
                $grandTotal = $total->getGrandTotal() + $consultationFee;
                $baseGrandTotal = $total->getBaseGrandTotal() + $consultationFee;
                $total->setGrandTotal($grandTotal);
                $total->setBaseGrandTotal($baseGrandTotal);
                $this->logger->info("After consultation fee calculation",
                [ 
                    'new_grand_total' => $grandTotal, 
                    'consultation_fee' => $consultationFee, 
                    'quote_id' => $quote->getId() 
                ]);
            } else { 
                $this->logger->info("No consultation fee to add",
                [ 
                    'quote_id' => $quote->getId(), 
                    'current_grand_total' => $total->getGrandTotal() 
                ]); 
            }
            
        return $this;
    }
    
    /** Fetch consultation fee total
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     * */

    public function fetch(Quote $quote, Total $total) { 
        $consultationFee = $quote->getData('consultation_fee');
        $this->logger->info("Consultation fee fetch operation",
        [ 
            'quote_id' => $quote->getId(), 
            'consultation_fee' => $consultationFee, 
            'current_grand_total' => $total->getGrandTotal() 
        ]);
        return [ 
            'code' => 'consultation_fee',
            'title' => __('Consultation Fee'),
            'value' => $consultationFee 
        ];
    } 
}