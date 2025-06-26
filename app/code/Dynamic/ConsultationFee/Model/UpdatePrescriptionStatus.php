<?php
namespace Dynamic\ConsultationFee\Model;

use Dynamic\ConsultationFee\Api\UpdatePrescriptionStatusInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\Quote\TotalsCollector;
use Psr\Log\LoggerInterface;
use Dynamic\ConsultationFee\Helper\Data as PaymentFeeHelper;

class UpdatePrescriptionStatus implements UpdatePrescriptionStatusInterface
{
    /**
     * @var PaymentFeeHelper
     */
    private $paymentFeeHelper;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var float
     */
    private $consultationFeeAmount;

    private const CONSULTATION_FEE_CODE = 'consultation_fee';

    /**
     * @param QuoteRepository $quoteRepository
     * @param PaymentFeeHelper $paymentFeeHelper
     * @param TotalsCollector $totalsCollector
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        PaymentFeeHelper $paymentFeeHelper,
        TotalsCollector $totalsCollector,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentFeeHelper = $paymentFeeHelper;
        $this->totalsCollector = $totalsCollector;
        $this->logger = $logger;
        $this->consultationFeeAmount = $this->paymentFeeHelper->getFeeAmount();
    }

    /**
     * Update prescription status and manage consultation fee
     *
     * @param int $cartId
     * @param bool $isPrescriptionRequired
     * @return array
     * @throws \Exception
     */
    public function execute($cartId, $isPrescriptionRequired)
    {
        try {
            $this->logger->info("Starting prescription status update process", [
                'cart_id' => $cartId,
                'is_prescription_required' => $isPrescriptionRequired
            ]);

            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setIsPrescriptionRequired($isPrescriptionRequired);
            $currentConsultationFee = (float)$quote->getData(self::CONSULTATION_FEE_CODE);

            $this->logger->info("Current consultation fee status", [
                'current_fee' => $currentConsultationFee
            ]);
            
            if ($isPrescriptionRequired) {
                if ($currentConsultationFee > 0) {
                    $this->logger->info("Removing consultation fee as prescription is required");
                    $quote->setData(self::CONSULTATION_FEE_CODE, 0);
                } else {
                    $this->logger->info("No consultation fee present to remove");
                }
            } else {
                if ($currentConsultationFee != $this->consultationFeeAmount) {
                    $this->logger->info("Adding consultation fee as prescription is not required", [
                        'fee_amount' => $this->consultationFeeAmount
                    ]);
                    $quote->setData(self::CONSULTATION_FEE_CODE, $this->consultationFeeAmount);
                } else {
                    $this->logger->info("Consultation fee already exists, no action needed");
                }
            }

            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            
            return [
                'success' => true,
                'message' => 'Prescription status updated successfully.',
                'consultation_fee' => (float)$quote->getData(self::CONSULTATION_FEE_CODE)
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error updating prescription status", [
                'cart_id' => $cartId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            throw $e;
        }
    }
}