<?php

namespace MedizinhubCore\Lab\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Razorpay\Api\Api;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use MedizinhubCore\Lab\Helper\RazorpayConfig;

class Success extends Action
{
    protected $orderRepository;
    protected $resultJsonFactory;
    protected $logger;
    protected $razorpayConfig;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        RazorpayConfig $razorpayConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->razorpayConfig = $razorpayConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $razorpayOrderId = $this->getRequest()->getParam('razorpay_order_id');
            $razorpayPaymentId = $this->getRequest()->getParam('razorpay_payment_id');
            $razorpaySignature = $this->getRequest()->getParam('razorpay_signature');
            $keyId = $this->razorpayConfig->getKeyId();
            $keySecret = $this->razorpayConfig->getKeySecret();
            $api = new Api($keyId, $keySecret);
            $attributes = [
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature'  => $razorpaySignature
            ];

            $api->utility->verifyPaymentSignature($attributes);
            $this->logger->info('Razorpay payment verification successful for order: ' . $razorpayOrderId);
            return $result->setData(['success' => true, 'message' => 'Payment verified successfully.']);
        } catch (\Exception $e) {
            $this->logger->error('Razorpay payment verification failed: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()]);
        }
    }
}
