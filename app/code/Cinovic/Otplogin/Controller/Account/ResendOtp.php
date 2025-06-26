<?php

namespace Cinovic\Otplogin\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Cinovic\Otplogin\Helper\Data as OtpHelper;
use Cinovic\Otplogin\Model\OtpFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\ResourceConnection;

class ResendOtp extends \Magento\Framework\App\Action\Action
{
    const OTP_LIMIT = 4;
    const OTP_UNLOCK_TIME = 180;

    protected $resultJsonFactory;
    protected $otpHelper;
    protected $otpFactory;
    protected $sessionManager;
    protected $customerRepository;
    protected $logger;
    private $curl;
    private $resourceConnection;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OtpHelper $otpHelper,
        OtpFactory $otpFactory,
        SessionManagerInterface $sessionManager,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        Curl $curl,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->otpHelper = $otpHelper;
        $this->otpFactory = $otpFactory;
        $this->sessionManager = $sessionManager;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->curl = $curl;
        $this->resourceConnection = $resourceConnection;
    }

    private function updateOtpInDb($mobileNumber, $otpCode, $expireTime)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('customer_otp');
        
        // Check if record exists
        $select = $connection->select()
            ->from($tableName)
            ->where('mobile_number = ?', $mobileNumber);
        
        $existingRecord = $connection->fetchRow($select);
        
        $expiresAt = date('Y-m-d H:i:s', time() + $expireTime);
        
        if ($existingRecord) {
            $data = [
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt
            ];
            
            $connection->update(
                $tableName,
                $data,
                ['mobile_number = ?' => $mobileNumber]
            );
        } else {
            $data = [
                'mobile_number' => $mobileNumber,
                'otp_code' => $otpCode,
                'attempts' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt
            ];
            
            $connection->insert($tableName, $data);
        }
    }

    private function canSendOtp($mobile)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('customer_otp');

        $select = $connection->select()
            ->from($tableName)
            ->where('mobile_number = ?', $mobile)
            ->where('attempts >= ?', self::OTP_LIMIT)
            ->where('created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)', self::OTP_UNLOCK_TIME);

        $result = $connection->fetchRow($select);

        return empty($result);
    }

    private function incrementOtpCount($mobile)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('customer_otp');

        $connection->update(
            $tableName,
            ['attempts' => new \Zend_Db_Expr('attempts + 1')],
            ['mobile_number = ?' => $mobile]
        );
    }

    public function execute()
    {
        try {
            $mode = $this->otpHelper->getMode();
            $expireTime = $this->otpHelper->getExpiretime();
            $AuthKey = $this->otpHelper->getAUthkey();
            $AuthToken = $this->otpHelper->getSmsmobile();
            $SenderId = $this->otpHelper->getSellerId();

            if ($mode == 'developer') {
                return $this->executeDeveloperMode($expireTime);
            } else {
                return $this->executeDefaultMode($expireTime, $AuthKey, $AuthToken, $SenderId);
            }
        } catch (LocalizedException $e) {
            $this->logger->error('Localized Exception:', ['exception' => $e]);
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->critical('Unexpected Exception:', ['exception' => $e]);
            $response = [
                'errors' => true,
                'message' => __('An unexpected error occurred. Please try again later.')
            ];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    private function executeDeveloperMode($expireTime)
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();
        $mobileNumber = $postData['mobile_number'] ?? $this->sessionManager->getMobileNumber();

        if (empty($mobileNumber)) {
            return $result->setData([
                'status' => 'error',
                'message' => __('Mobile number is required.'),
            ]);
        }

        if (!$this->canSendOtp($mobileNumber)) {
            return $this->createErrorResponse(__('You have reached the maximum OTP requests.'));
        }

        try {
            $otpCode = random_int(100000, 999999);
            $this->updateOtpInDb($mobileNumber, $otpCode, $expireTime);
            $this->incrementOtpCount($mobileNumber);

            return $result->setData([
                'status' => 'success',
                'otp_code' => $otpCode,
                'message' => __('OTP has been resent to your mobile number.'),
                'expire_time' => $expireTime
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'status' => 'error',
                'message' => __('Failed to resend OTP. Please try again later.'),
            ]);
        }
    }

    private function executeDefaultMode($expireTime, $AuthKey, $AuthToken, $SenderId)
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();
        $mobileNumber = $postData['mobile_number'] ?? $this->sessionManager->getMobileNumber();

        if (empty($mobileNumber)) {
            return $result->setData([
                'status' => 'error',
                'message' => __('Mobile number is required.'),
            ]);
        }

        if (!$this->canSendOtp($mobileNumber)) {
            return $this->createErrorResponse(__('You have reached the maximum OTP requests.'));
        }

        try {
            $otpCode = random_int(100000, 999999);
            $this->updateOtpInDb($mobileNumber, $otpCode, $expireTime);
            
            $this->sendOtpViaSmsCountry($otpCode, $mobileNumber, $expireTime, $AuthKey, $AuthToken, $SenderId);
            $this->incrementOtpCount($mobileNumber);

            return $result->setData([
                'status' => 'success',
                'message' => __('OTP has been resent to your mobile number.'),
                'expire_time' => $expireTime
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'status' => 'error',
                'message' => __($e->getMessage()),
            ]);
        }
    }

    private function sendOtpViaSmsCountry($otp_code, $mobileNumber, $expireTime, $AuthKey, $AuthToken, $SenderId)
    {
        $expiry_time_in_minutes = $expireTime / 60;
        $message = "Dear MedizinHub Customer, Your one time password is $otp_code to Register your account and is valid for $expiry_time_in_minutes mins. www.medizinhub.com";
        $requestData = [
            'Text' => $message,
            'Number' => $mobileNumber,
            'SenderId' => $SenderId,
            'DRNotifyUrl' => 'https://www.domainname.com/notifyurl',
            'DRNotifyHttpMethod' => 'POST',
            'Tool' => 'API'
        ];
        $apiUrl = "https://restapi.smscountry.com/v0.1/Accounts/$AuthKey/SMSes/";
        $headers = [
            'Authorization: Basic ' . base64_encode("$AuthKey:$AuthToken"),
            'Content-Type: application/json'
        ];
        try {
            $this->curl->setOption(\CURLOPT_HTTPHEADER, $headers);
            $this->curl->setOption(\CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(\CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(\CURLOPT_FOLLOWLOCATION, true);
            $this->curl->post($apiUrl, json_encode($requestData));

            $responseBody = $this->curl->getBody();
            $responseCode = $this->curl->getStatus();

            if ($responseCode === 202) {
                return [
                    'otp' => $otp_code,
                    'mobile_number' => $mobileNumber,
                    'message' => __('OTP sent to your Mobile Number')
                ];
            } else {
                $this->logger->error('SMS API Error - Status Code: ' . $responseCode);
                $this->logger->error('SMS API Response: ' . $responseBody);
                throw new \Exception('Error sending OTP via SMS API.');
            }
        } catch (\Exception $e) {
            $this->logger->error('SMS API Error:', ['exception' => $e]);
            throw new \Exception('Error sending OTP via SMS API.');
        }
    }

    /**
     * Create error response
     *
     * @param string $message
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function createErrorResponse($message)
    {
        return $this->resultJsonFactory->create()->setData([
            'errors' => true,
            'message' => $message
        ]);
    }
}