<?php
namespace MedizinhubCore\Patient\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Razorpay\Api\Api;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use MedizinhubCore\Patient\Helper\RazorpayConfig;

class Razorpay extends Action
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

    public function execute() {
        $result = $this->resultJsonFactory->create();
        
        try {
            $doctorFee = $this->getRequest()->getParam('doctor_fee');
                    
            if ($doctorFee === null) {
                throw new \Exception('Doctor fee is missing');
            }
            if (!is_numeric($doctorFee)) {
                throw new \Exception('Invalid doctor fee: ' . $doctorFee);
            }
            
            $keyId = $this->razorpayConfig->getKeyId();
            $keySecret = $this->razorpayConfig->getKeySecret();
            
            if (empty($keyId) || empty($keySecret)) {
                throw new \Exception('Razorpay API credentials are not configured');
            }
            
            $api = new Api($keyId, $keySecret);
            $amountInPaise = intval(round(floatval($doctorFee) * 100));
            if ($amountInPaise <= 0) {
                throw new \Exception('Invalid payment amount');
            }
            
            $orderData = [
                'receipt'         => 'MHBAPP_' . uniqid(),
                'amount'          => $amountInPaise,
                'currency'        => 'INR',
                'payment_capture' => 1
            ];
            
            $razorpayOrder = $api->order->create($orderData);
            $razorpayOrderId = $razorpayOrder['id'];
            
            return $result->setData([
                'success' => true,
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_key' => $keyId, // Add this line to include the key ID in the response
                'message' => 'Order created successfully'
            ]);
            
        } catch (\Razorpay\Api\Errors\Error $e) {
            $this->logger->error('Razorpay API Error: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => 'Razorpay API Error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Razorpay Order Creation Error: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ]);
        }
    }
}