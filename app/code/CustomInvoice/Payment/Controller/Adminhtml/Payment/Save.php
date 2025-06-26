<?php
namespace CustomInvoice\Payment\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use CustomInvoice\Payment\Model\PaymentFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    protected $paymentFactory;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        PaymentFactory $paymentFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->paymentFactory = $paymentFactory;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();

        try {
            $this->validateRequiredFields($data);
            $payment = $this->paymentFactory->create();
            $paymentData = $this->preparePaymentData($data);
            $payment->setData($paymentData);
            $payment->save();
                        
            return $resultJson->setData([
                'success' => true,
                'message' => __('Payment saved successfully'),
                'entity_id' => $payment->getId()
            ]);
            
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate required fields before saving
     * 
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateRequiredFields($data)
    {
        $requiredFields = [
            'customer_name',
            'customer_phone',
            'total_amount',
            'razorpay_payment_id'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new LocalizedException(
                    __('Required field "%1" is missing', $field)
                );
            }
        }
    }

    /**
     * Prepare payment data for saving
     * 
     * @param array $data
     * @return array
     */
    protected function preparePaymentData($data)
    {
        $paymentData = [
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'payment_type' => $data['payment_type'] ?? null,
            'total_amount' => $data['total_amount'] ?? 0,
            'cash_amount' => $data['cash_amount'] ?? 0,
            'online_amount' => $data['online_amount'] ?? 0,
            'razorpay_transaction_id' => $data['razorpay_payment_id'] ?? null,
            'payment_description' => $data['payment_description'] ?? 'Razorpay Online Payment'
        ];

        return $paymentData;
    }

    /**
     * Check if the action is allowed
     * 
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CustomInvoice_Payment::payment');
    }
}