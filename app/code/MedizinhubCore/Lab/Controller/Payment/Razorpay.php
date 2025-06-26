<?php
namespace MedizinhubCore\Lab\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Razorpay\Api\Api;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use MedizinhubCore\Lab\Helper\RazorpayConfig;

class Razorpay extends Action {
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

    public function execute() {
        $result = $this->resultJsonFactory->create();

        try {
            // Retrieve parameters
            $orderId = $this->getRequest()->getParam('order_id');
            $totalPrice = $this->getRequest()->getParam('total_price');

            // Validate total price more strictly
            if (!$totalPrice || !is_numeric($totalPrice) || $totalPrice <= 0) {
                throw new \Exception(__('Invalid total price. Must be a positive number.'));
            }

            // Convert to paise (smallest currency unit)
            $amountInPaise = (int)($totalPrice * 100);

            // Extensive logging for debugging
            $this->logger->info('Razorpay Order Creation Request', [
                'orderId' => $orderId,
                'totalPrice' => $totalPrice,
                'amountInPaise' => $amountInPaise
            ]);

            // Retrieve and validate API credentials
            $keyId = $this->razorpayConfig->getKeyId();
            $keySecret = $this->razorpayConfig->getKeySecret();

            // Log masked API key for security
            $this->logger->info('Razorpay API Key Check', [
                'keyId' => substr($keyId, 0, 4) . '***',
                'keySecret' => $keySecret ? 'Present' : 'Missing'
            ]);

            if (empty($keyId) || empty($keySecret)) {
                throw new \Exception('Razorpay API credentials are not configured.');
            }

            // Initialize Razorpay API
            $api = new Api($keyId, $keySecret);

            // Prepare order data
            $orderData = [
                'receipt'         => $orderId ?? 'MHBAPP_' . date('YmdHis') . '_' . uniqid(),
                'amount'          => $amountInPaise,
                'currency'        => 'INR',
                'payment_capture' => 1,
            ];

            // Log order creation payload
            $this->logger->info('Razorpay Order Data', $orderData);

            // Create Razorpay order
            try {
                $razorpayOrder = $api->order->create($orderData);
            } catch (\Exception $createException) {
                // Log any exceptions during order creation
                $this->logger->error('Razorpay Order Creation Exception', [
                    'message' => $createException->getMessage(),
                    'trace' => $createException->getTraceAsString()
                ]);
                throw $createException;
            }

            // Validate Razorpay order creation
            if (!isset($razorpayOrder['id'])) {
                throw new \Exception('Razorpay order ID not generated.');
            }

            // Return successful response
            return $result->setData([
                'success' => true,
                'razorpay_order_id' => $razorpayOrder['id'],
                'message' => 'Order created successfully.',
            ]);

        } catch (\Razorpay\Api\Errors\Error $e) {
            // Detailed logging for Razorpay API specific errors
            $this->logger->error('Razorpay API Detailed Error', [
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'full_error' => json_encode($e)
            ]);

            return $result->setData([
                'success' => false,
                'message' => 'Razorpay API Error: ' . $e->getMessage(),
                'error_details' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ]);

        } catch (\Exception $e) {
            // Comprehensive logging for general exceptions
            $this->logger->error('Razorpay Order Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $result->setData([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ]);
        }
    }
}
