<?php
namespace Dynamic\ConsultationFee\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdatePrescriptionStatus implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $cartRepository;
    protected $request;
    protected $checkoutSession;

    public function __construct(
        JsonFactory $jsonFactory,
        CartRepositoryInterface $cartRepository,
        RequestInterface $request,
        CheckoutSession $checkoutSession
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cartRepository = $cartRepository;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            $isPrescriptionRequired = $this->request->getParam('isPrescriptionRequired');
            $quote = $this->checkoutSession->getQuote();

            if (!$quote->getId()) {
                throw new NoSuchEntityException(__('Cart does not exist'));
            }

            $quote->setIsPrescriptionRequired($isPrescriptionRequired);
            $quote->collectTotals();
            $this->cartRepository->save($quote);

            $totals = $quote->getTotals();
            $consultationFee = isset($totals['consultation_fee']) ? $totals['consultation_fee']->getValue() : 0;

            return $resultJson->setData([
                'success' => true,
                'message' => __('Prescription status updated successfully.'),
                'consultation_fee' => $consultationFee
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
