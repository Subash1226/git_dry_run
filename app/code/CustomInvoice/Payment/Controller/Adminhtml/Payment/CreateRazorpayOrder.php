<?php
namespace CustomInvoice\Payment\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Razorpay\Api\Api;
use MedizinhubCore\Patient\Helper\RazorpayConfig;

class CreateRazorpayOrder extends Action
{
    protected $jsonFactory;
    protected $razorpayConfig;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RazorpayConfig $razorpayConfig
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->razorpayConfig = $razorpayConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();

        $keyId = $this->razorpayConfig->getKeyId();
        $keySecret = $this->razorpayConfig->getKeySecret();

        if (empty($keyId) || empty($keySecret)) {
            return $resultJson->setData([
                'success' => false,
                'message' => 'Razorpay API credentials are not configured'
            ]);
        }

        try {
            $api = new Api($keyId, $keySecret);

            $orderData = [
                'receipt'         => 'Mhb_' . time(),
                'amount'          => $data['online_amount'] * 100,
                'currency'        => 'INR',
                'payment_capture' => 1 
            ];

            $razorpayOrder = $api->order->create($orderData);

            return $resultJson->setData([
                'success'         => true,
                'order_id'        => $razorpayOrder['id'],
                'amount'          => $data['online_amount'] * 100,
                'currency'        => 'INR',
                'customer_name'   => $data['customer_name'],
                'customer_address'   => $data['customer_address'],
                'payment_description'   => $data['payment_description'],
                'payment_type'   => $data['payment_type'],
                'total_amount'   => $data['total_amount'],
                'cash_amount'   => $data['cash_amount'],
                'online_amount'   => $data['online_amount'],
                'customer_email'  => $data['customer_email'] ?? '',
                'customer_phone'  => $data['customer_phone']
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}