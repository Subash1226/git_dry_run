<?php
namespace CustomInvoice\Payment\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Razorpay\Api\Api;
use CustomInvoice\Payment\Model\PaymentFactory;
use MedizinhubCore\Patient\Helper\RazorpayConfig;

class Verify extends Action
{
    protected $jsonFactory;
    protected $razorpayApi;
    protected $paymentFactory;
    protected $razorpayConfig;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PaymentFactory $paymentFactory,
        RazorpayConfig $razorpayConfig
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->paymentFactory = $paymentFactory;
        $this->razorpayConfig = $razorpayConfig;      

        $keyId = $this->razorpayConfig->getKeyId();
        $keySecret = $this->razorpayConfig->getKeySecret();
        $this->razorpayApi = new Api($keyId, $keySecret);
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();

        try {
            $this->razorpayApi->utility->verifyPaymentSignature([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_order_id'   => $data['razorpay_order_id'],
                'razorpay_signature'  => $data['razorpay_signature']
            ]);

            $payment = $this->razorpayApi->payment->fetch($data['razorpay_payment_id']);

            return $resultJson->setData([
                'success' => true,
                'message' => 'Payment verified successfully'
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}