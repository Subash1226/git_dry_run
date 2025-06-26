<?php
namespace Cinovic\Otplogin\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;
use Cinovic\Otplogin\Helper\Data as OtploginHelper;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class OtpPost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OtploginHelper
     */
    protected $otploginHelper;

    /**
     * @var ResourceConnection
     */
    private $resource;    
    private $customerDataFactory;
    private $accountManagement;

    /**
     * @param Context $context
     * @param SessionManagerInterface $sessionManager
     * @param CustomerFactory $customerFactory
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     * @param OtploginHelper $otploginHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        CustomerFactory $customerFactory,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        CustomerInterfaceFactory $customerDataFactory,
        AccountManagementInterface $accountManagement,
        LoggerInterface $logger,
        OtploginHelper $otploginHelper,
        ResourceConnection $resource
    ) {
        $this->_sessionManager = $sessionManager;
        $this->_customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerDataFactory = $customerDataFactory;
        $this->logger = $logger;
        $this->otploginHelper = $otploginHelper;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $mode = $this->otploginHelper->getMode();
        $this->logger->info('OTP Login: Current mode is ' . $mode);

        if ($mode == 'developer') {
            return $this->executeDeveloperMode();
        } else {
            return $this->executeDefaultMode();
        }
    }

    /**
     * Execute in developer mode
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function executeDeveloperMode()
    {
        try {
            $mobileNumber = $this->_sessionManager->getMobileNumber();
            $otpByUser = $this->getRequest()->getParam('otp');
            $expectedEmail = $this->_sessionManager->getEmail();
            $CustomerStatus = $this->_sessionManager->getCustomerStatus();

            $this->logger->info('Developer Mode: Received data', [
                'mobile_number' => $mobileNumber,
                'otp' => $otpByUser,
                'email' => $expectedEmail,
                'customer_status' => $CustomerStatus,
            ]);

            $expectedOTP = $this->getOtpFromDatabaseDev($mobileNumber);

            if (!$expectedOTP) {
                throw new LocalizedException(__('OTP has expired'));
            }

            if ($otpByUser == $expectedOTP) {
                if ($CustomerStatus == 0) {
                    $customerData = $this->customerDataFactory->create();
                    $customerData->setFirstname('Default');
                    $customerData->setLastname('User');
                    $customerData->setEmail($this->generateUniqueEmail($mobileNumber));
                    $customerData->setCustomAttribute('mobile_number', $mobileNumber);
                    $newPassword = $this->generateCompliantPassword();
                    $newCustomer = $this->accountManagement->createAccount($customerData, $newPassword);
                    $this->_sessionManager->unsEmail();
                    $this->_sessionManager->unsCustomerStatus();
                    return $this->createSuccessResponse([
                        'message' => __('Otp Verified Successfully')
                    ]);
                } else if($CustomerStatus == 2){
                    return $this->createSuccessResponse([
                        'message' => __('Otp Verified Successfully')
                    ]);
                } else {
                    return $this->processCustomerLogin($expectedEmail);
                }
            }

            return $this->createErrorResponse(__('Please Enter Your Valid 6 Digit OTP!'));
        } catch (\Exception $e) {
            $this->logger->critical('Error in developer mode: ' . $e->getMessage());
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Execute in default mode
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function executeDefaultMode()
    {
        try {
            $mobileNumber = $this->_sessionManager->getMobileNumber();
            $otpByUser = $this->getRequest()->getParam('otp');
            $expectedEmail = $this->_sessionManager->getEmail();
            $CustomerStatus = $this->_sessionManager->getCustomerStatus();

            $this->logger->info('Developer Mode: Received data', [
                'mobile_number' => $mobileNumber,
                'otp' => $otpByUser,
                'email' => $expectedEmail,
                'customer_status' => $CustomerStatus,
            ]);

            $expectedOTP = $this->getOtpFromDatabase($mobileNumber);

            if (!$expectedOTP) {
                throw new LocalizedException(__('OTP has expired'));
            }

            if ($otpByUser == $expectedOTP) {
                if ($CustomerStatus == 0) {
                    $customerData = $this->customerDataFactory->create();
                    $customerData->setFirstname('Default');
                    $customerData->setLastname('User');
                    $customerData->setEmail($this->generateUniqueEmail($mobileNumber));
                    $customerData->setCustomAttribute('mobile_number', $mobileNumber);
                    $newPassword = $this->generateCompliantPassword();
                    $newCustomer = $this->accountManagement->createAccount($customerData, $newPassword);
                    $this->_sessionManager->unsEmail();
                    $this->_sessionManager->unsCustomerStatus();
                    return $this->createSuccessResponse([
                        'message' => __('Otp Verified Successfully')
                    ]);
                } else if($CustomerStatus == 2){
                    return $this->createSuccessResponse([
                        'message' => __('Otp Verified Successfully')
                    ]);
                } else {
                    return $this->processCustomerLogin($expectedEmail);
                }
            }

            return $this->createErrorResponse(__('Please Enter Your Valid 6 Digit OTP!'));
        } catch (\Exception $e) {
            $this->logger->critical('Error in default mode: ' . $e->getMessage());
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Get OTP from database with validation (Developer Mode)
     *
     * @param string $mobileNumber
     * @return string|null
     */
    private function getOtpFromDatabaseDev($mobileNumber)
    {
        try {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('customer_otp');

            // Get valid OTP record without checking attempts
            $select = $connection->select()
                ->from($tableName)
                ->where('mobile_number = ?', $mobileNumber)
                ->where('expires_at > ?', new \Zend_Db_Expr('CURRENT_TIMESTAMP'))
                ->order('created_at DESC')
                ->limit(1);

            $result = $connection->fetchRow($select);

            if (!$result) {
                $this->logger->warning('No valid OTP found', [
                    'mobile_number' => $mobileNumber
                ]);
                return null;
            }

            $this->logger->info('Valid OTP retrieved in developer mode', [
                'mobile_number' => $mobileNumber,
                'expires_at' => $result['expires_at']
            ]);

            return $result['otp_code'];

        } catch (\Exception $e) {
            $this->logger->critical('Error retrieving OTP in developer mode: ' . $e->getMessage(), [
                'mobile_number' => $mobileNumber,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get OTP from database with validation (Default Mode)
     *
     * @param string $mobileNumber
     * @return string|null
     */
    private function getOtpFromDatabase($mobileNumber)
    {
        try {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('customer_otp');

            // Get valid OTP record with attempts validation
            $select = $connection->select()
                ->from($tableName)
                ->where('mobile_number = ?', $mobileNumber)
                ->where('expires_at > ?', new \Zend_Db_Expr('CURRENT_TIMESTAMP'))
                ->where('attempts < ?', 5)
                ->order('created_at DESC')
                ->limit(1);

            $result = $connection->fetchRow($select);

            if (!$result) {
                $this->logger->warning('No valid OTP found or maximum attempts exceeded', [
                    'mobile_number' => $mobileNumber
                ]);
                return null;
            }

            // Update attempts count
            $connection->update(
                $tableName,
                ['attempts' => new \Zend_Db_Expr('attempts + 1')],
                ['entity_id = ?' => $result['entity_id']]
            );

            $this->logger->info('Valid OTP retrieved', [
                'mobile_number' => $mobileNumber,
                'attempts' => $result['attempts'] + 1,
                'expires_at' => $result['expires_at']
            ]);

            return $result['otp_code'];

        } catch (\Exception $e) {
            $this->logger->critical('Error retrieving OTP: ' . $e->getMessage(), [
                'mobile_number' => $mobileNumber,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Process customer login
     *
     * @param string $email
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function processCustomerLogin($email)
    {
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);

        if (!$customer->getId()) {
            return $this->createErrorResponse(__('Customer not found for provided email.'));
        }

        $this->customerSession->setCustomerAsLoggedIn($customer);
        $this->customerSession->regenerateId();
        $this->_sessionManager->unsEmail();
        $this->_sessionManager->unsCustomerStatus();

        return $this->createSuccessResponse([
            'message' => __('Logged In Successfully.'),
            'customer_id' => $customer->getId()
        ]);
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

    /**
     * Create success response
     *
     * @param array $data
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function createSuccessResponse($data)
    {
        return $this->resultJsonFactory->create()->setData(array_merge(
            ['errors' => false],
            $data
        ));
    }

    private function generateUniqueEmail($mobileNumber)
    {
        return $mobileNumber . '@mobile.com';
    }

    private function generateCompliantPassword()
    {
        return 'Medizinhub@user123';
    }
}