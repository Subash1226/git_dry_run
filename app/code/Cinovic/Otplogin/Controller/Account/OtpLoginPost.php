<?php

namespace Cinovic\Otplogin\Controller\Account;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Cinovic\Otplogin\Helper\Data as OtploginHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class OtpLoginPost extends \Magento\Framework\App\Action\Action
{
    const OTP_LIMIT = 3;
    const OTP_UNLOCK_TIME = 600;

    private $resultJsonFactory;
    private $sessionManager;
    private $customerRepository;
    private $customerDataFactory;
    private $accountManagement;
    private $customerRegistry;
    private $resourceConnection;
    private $logger;
    private $otploginHelper;
    private $curl;
    private $encryptor;
    private $transportBuilder;
    private $inlineTranslation;
    private $storeManager;
    private $scopeConfig;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $sessionManager,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
        CustomerRegistry $customerRegistry,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        OtploginHelper $otploginHelper,
        Curl $curl,
        EncryptorInterface $encryptor,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionManager = $sessionManager;
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRegistry = $customerRegistry;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->otploginHelper = $otploginHelper;
        $this->curl = $curl;
        $this->encryptor = $encryptor;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    private function storeOtpInDb($mobileNumber, $otpCode, $expireTime)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('customer_otp');
        $connection->delete($tableName, ['mobile_number = ?' => $mobileNumber]);
        $expiresAt = date('Y-m-d H:i:s', time() + $expireTime);
        $data = [
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'attempts' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt
        ];
        $connection->insert($tableName, $data);
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
            $mode = $this->otploginHelper->getMode();
            $expireTime = $this->otploginHelper->getExpiretime();
            $AuthKey = $this->otploginHelper->getAUthkey();
            $AuthToken = $this->otploginHelper->getSmsmobile();
            $SenderId = $this->otploginHelper->getSellerId();

            if ($mode == 'developer') {
                return $this->handleDeveloperMode($expireTime);
            } else {
                return $this->handleDefaultMode($expireTime, $AuthKey, $AuthToken, $SenderId);
            }
        } catch (LocalizedException $e) {
            $this->logger->error('Localized Exception:', ['exception' => $e]);
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (NoSuchEntityException $e) {
            $this->logger->error('No Such Entity Exception:', ['exception' => $e]);
            $response = [
                'errors' => true,
                'message' => __('Customer not found.')
            ];
        } catch (\Exception $e) {
            $this->logger->critical('Unexpected Exception:', ['exception' => $e]);
            $response = [
                'errors' => true,
                'message' => __('An unexpected error occurred. Please try again later.')
            ];
        }
        return $this->createJsonResponse($response);
    }

    private function handleDeveloperMode($expireTime)
    {
        $mobile = $this->getRequest()->getParam('mobile_number');
        if (!$mobile) {
            return $this->createJsonResponse([
                'errors' => true,
                'message' => __('Mobile number is required.')
            ]);
        }

        if (!$this->canSendOtp($mobile)) {
            return $this->createJsonResponse([
                'errors' => true,
                'message' => __('You have reached the maximum number of OTP requests. Please try again after 10 minutes.')
            ], 400);
        }

        $mobileNumber = '91' . $mobile;
        $customerId = $this->getCustomerIdByMobileNumber($mobileNumber);
        $this->logger->info('Customer ID:', ['id' => $customerId]);
        $otp_code = random_int(100000, 999999);
        $this->storeOtpInDb($mobileNumber, $otp_code, $expireTime);
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer->getFirstname() == 'Default') {
                $this->sessionManager->setMobileNumber($mobileNumber);
                $this->sessionManager->setCustomerStatus(2);

                $response = [
                    'otp' => $otp_code,
                    'customer_status' => 2,
                    'mobile_number' => $mobileNumber,
                    'expire_time' => $expireTime,
                    'message' => __('OTP sent successfully!')
                ];
                return $this->createJsonResponse($response);
            }
            $this->sessionManager->setMobileNumber($mobileNumber);
            $this->sessionManager->setEmail($customer->getEmail());
            $this->sessionManager->setCustomerStatus(1);

            $response = [
                'otp' => $otp_code,
                'customer_status' => 1,
                'expire_time' => $expireTime,
                'mobile_number' => $mobileNumber,
                'message' => __('OTP sent successfully!')
            ];
        } else {
            $this->sessionManager->setMobileNumber($mobileNumber);
            $this->sessionManager->setCustomerStatus(0);
            $response = [
                'otp' => $otp_code,
                'customer_status' => 0,
                'mobile_number' => $mobileNumber,
                'expire_time' => $expireTime,
                'message' => __('OTP sent successfully!')
            ];
        }

        $this->incrementOtpCount($mobile);
        return $this->createJsonResponse($response);
    }

    private function handleDefaultMode($expireTime, $AuthKey, $AuthToken, $SenderId)
    {
        $mobile = $this->getRequest()->getParam('mobile_number');
        if (!$mobile) {
            return $this->createJsonResponse([
                'errors' => true,
                'message' => __('Mobile number is required.')
            ]);
        }
        if (!$this->canSendOtp($mobile)) {
            return $this->createJsonResponse([
                'errors' => true,
                'message' => __('You have reached the maximum number of OTP requests. Please try again after 10 minutes.')
            ], 400);
        }
        $mobileNumber = '91' . $mobile;
        $customerId = $this->getCustomerIdByMobileNumber($mobileNumber);
        $this->logger->info('Customer ID:', ['id' => $customerId]);
        $otp_code = random_int(100000, 999999);
        $this->storeOtpInDb($mobileNumber, $otp_code, $expireTime);

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer->getFirstname() == 'Default') {
                $this->sessionManager->setMobileNumber($mobileNumber);
                $this->sessionManager->setCustomerStatus(2);
                $this->sendOtpViaSmsCountry($otp_code, $mobileNumber, $expireTime, $AuthKey, $AuthToken, $SenderId);
                $response = [
                    'customer_status' => 2,
                    'mobile_number' => $mobileNumber,
                    'expire_time' => $expireTime,
                    'message' => __('OTP sent successfully!')
                ];
                return $this->createJsonResponse($response);
            }
            $this->sessionManager->setMobileNumber($mobileNumber);
            $this->sessionManager->setEmail($customer->getEmail());
            $this->sessionManager->setCustomerStatus(1);
            $otpResponse = $this->sendOtpViaSmsCountry($otp_code, $mobileNumber, $expireTime, $AuthKey, $AuthToken, $SenderId);
            if ($otpResponse['status'] !== 202) {
                $response = [
                    'customer_status' => 1,
                    'email' => $otpResponse['email'],
                    'expire_time' => $expireTime,
                    'message' => $otpResponse['message']
                ];
                return $this->createJsonResponse($response);
            }
            $response = [
                'customer_status' => 1,
                'mobile_number' => $mobileNumber,
                'expire_time' => $expireTime,
                'message' => __('OTP sent successfully!')
            ];
        } else {
            $this->sessionManager->setMobileNumber($mobileNumber);
            $this->sessionManager->setCustomerStatus(0);
            $otpResponse =  $this->sendOtpViaSmsCountry($otp_code, $mobileNumber, $expireTime, $AuthKey, $AuthToken, $SenderId);
            if ($otpResponse['status'] !== 202) {
                $response = [
                    'customer_status' => 0,
                    'mobile_number' => $mobileNumber,
                    'expire_time' => $expireTime,
                    'message' => $otpResponse['message'],
                    'newuser' => $otpResponse['newuser']
                ];
                return $this->createJsonResponse($response);
            }
            $response = [
                'customer_status' => 0,
                'mobile_number' => $mobileNumber,
                'expire_time' => $expireTime,
                'message' => __('OTP sent successfully!')
            ];
        }

        $this->incrementOtpCount($mobile);
        return $this->createJsonResponse($response);
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
                return ['status' => 202, 'message' => 'OTP sent successfully!'];
            }
            if (in_array($responseCode, [404, 504, 500])) {
                $this->logger->error("SMS API Error - Status Code: $responseCode");
                $customerEmail = $this->getCustomerEmailByMobileNumber($mobileNumber);
                if ($customerEmail) {
                    $this->sendOtpViaEmail($otp_code, $customerEmail, $expiry_time_in_minutes);
                    return ['status' => $responseCode,'email' => $customerEmail, 'message' => 'OTP failed via SMS. Sent via email.'];
                }else{
                    $this->sendOtpViaWhatsApp($otp_code, $mobileNumber);
                    return ['newuser' => true,'status' => $responseCode,'mobile' => $mobileNumber, 'message' => 'OTP failed via SMS. Sent via WhatsApp.'];
                }
            }
        $this->logger->error('SMS API Error - Status Code: ' . $responseCode);
        $this->logger->error('SMS API Response: ' . $responseBody);
        throw new \Exception('Error sending OTP via SMS API.');
    } catch (\Exception $e) {
        $this->logger->error('SMS API Error:', ['exception' => $e]);
        throw new \Exception('Error sending OTP via SMS API.');
    }
    }

    private function createJsonResponse($data)
    {
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($data);
    }

    private function getCustomerIdByMobileNumber($mobileNumber)
    {
        $connection = $this->resourceConnection->getConnection();
        $eavAttributeTable = $connection->getTableName('eav_attribute');
        $customerEntityVarcharTable = $connection->getTableName('customer_entity_varchar');
        $selectAttributeId = $connection->select()
            ->from($eavAttributeTable, ['attribute_id'])
            ->where('attribute_code = ?', 'mobile_number')
            ->where('entity_type_id = ?', 1);

        $attributeId = $connection->fetchOne($selectAttributeId);
        $selectCustomerId = $connection->select()
            ->from($customerEntityVarcharTable, ['entity_id'])
            ->where('attribute_id = ?', $attributeId)
            ->where('value = ?', $mobileNumber);

        return $connection->fetchOne($selectCustomerId);
    }
    private function getCustomerEmailByMobileNumber($mobileNumber)
{
    $connection = $this->resourceConnection->getConnection();
    $customerId = $this->getCustomerIdByMobileNumber($mobileNumber);
    if (!$customerId) {
        $this->logger->info("No customer ID found for mobile number: $mobileNumber");
        return null;
    }
    $customerEntityTable = $connection->getTableName('customer_entity');
    $selectEmail = $connection->select()
        ->from($customerEntityTable, ['email'])
        ->where('entity_id = ?', $customerId);

    try {
        $email = $connection->fetchOne($selectEmail);
        if ($email) {
            $this->logger->info("Retrieved email for customer ID $customerId: $email");
        } else {
            $this->logger->warning("No email found for customer ID $customerId");
        }
        return $email;
    } catch (\Exception $e) {
        $this->logger->error('Error retrieving email for customer ID ' . $customerId, ['exception' => $e]);
        return null;
    }
}

/**
 * Send OTP via email with proper inline translation handling
 *
 * @param string $otp_code
 * @param string $email
 * @param string $expireTime
 * @return bool
 */
private function sendOtpViaEmail($otp_code, $email, $expiry_time_in_minutes)
{
    try {
        $this->inlineTranslation->suspend();
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        $fromEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $fromName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $templateVars = [
            'otp_code' => $otp_code,
            'expire_time' => __($expiry_time_in_minutes),
            'store' => $store,
            'email_heading' => __('Your OTP Code'),
            'email_description' => __('Please use the following OTP code to verify your account:'),
            'expire_notice' => __('This code will expire in 3 Min.')
        ];
        $transport = $this->transportBuilder
            ->setTemplateIdentifier('otp_email_template')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope([
                'name' => $fromName,
                'email' => $fromEmail
            ])
            ->addTo($email)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
        $this->logger->info("OTP sent successfully via email to: " . $email);
        return true;
    } catch (\Exception $e) {
        $this->inlineTranslation->resume();
        $this->logger->error('Error sending OTP via email:', [
            'exception' => $e,
            'email' => $email,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}

 /**
     * Call WhatsApp API to send message
     *
     * @param string $mobileNumber
     * @return void
     */
    protected function sendOtpViaWhatsApp($mobileNumber, $otp_code)
    {
        $url = 'https://backend.aisensy.com/campaign/t1/api/v2';
        $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY3NjUyMTRiMmQ3OGRhMTAwM2EwYWM1OSIsIm5hbWUiOiJNZWRpemluSHViIFBoYXJtYWN5IiwiYXBwTmFtZSI6IkFpU2Vuc3kiLCJjbGllbnRJZCI6IjY3NjUyMTRiMmQ3OGRhMTAwM2EwYWM1NCIsImFjdGl2ZVBsYW4iOiJGUkVFX0ZPUkVWRVIiLCJpYXQiOjE3MzQ2ODA5MDd9.8wZzPPq9YFLRExO10riSmg57cEyNUC3GW8FeBPbwmRw';
        $campaignName = 'customer_otp_service_new';
        $templateParams = [$otp_code];

        $postData = [
            'apiKey' => $apiKey,
            'campaignName' => $campaignName,
            'destination' => $mobileNumber,
            'userName' => 'MedizinHub',
            'templateParams' => $templateParams
        ];
        try {
            $this->curl->setHeaders(['Content-Type' => 'application/json']);
            $this->curl->post($url, json_encode($postData));
            $response = $this->curl->getBody();
            $this->logger->info('WhatsApp API response: ' . $response);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send WhatsApp message: ' . $e->getMessage());
        }
    }
}
