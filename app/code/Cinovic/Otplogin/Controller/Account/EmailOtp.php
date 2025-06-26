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
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EmailOtp extends \Magento\Framework\App\Action\Action
{
    protected $_sessionManager;
    protected $_customerFactory;
    protected $resultJsonFactory;
    protected $customerSession;
    protected $logger;
    protected $otploginHelper;
    protected $resource;
    protected $customerDataFactory;
    protected $accountManagement;
    protected $inlineTranslation;
    protected $transportBuilder;
    protected $storeManager;
    protected $scopeConfig;

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
        ResourceConnection $resource,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_sessionManager = $sessionManager;
        $this->_customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->customerDataFactory = $customerDataFactory;
        $this->accountManagement = $accountManagement;
        $this->logger = $logger;
        $this->otploginHelper = $otploginHelper;
        $this->resource = $resource;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            // Get POST parameters
            $email = $this->getRequest()->getParam('email');
            $mobileNumber = $this->getRequest()->getParam('mobileNumber');

            if (!$email || !$mobileNumber) {
                throw new LocalizedException(__('Email and mobile number are required.'));
            }

            // Get connection and table name
            $connection = $this->resource->getConnection();
            $tableName = $connection->getTableName('customer_otp');

            // Get existing OTP record for the mobile number
            $select = $connection->select()
                ->from($tableName)
                ->where('mobile_number = ?', $mobileNumber)
                ->where('expires_at > ?', date('Y-m-d H:i:s'))
                ->order('created_at DESC')
                ->limit(1);

            $otpRecord = $connection->fetchRow($select);

            if ($otpRecord) {
                // Send OTP via email
                $emailSent = $this->sendOtpEmail($email, $otpRecord['otp_code']);

                if (!$emailSent) {
                    throw new LocalizedException(__('Failed to send OTP via email.'));
                }

                $this->_sessionManager->setOtpEmail($email);
                $this->_sessionManager->setSessionOtpEmail($email);

                $response = [
                    'success' => true,
                    'message' => __('OTP sent successfully to your email'),
                    'email' => $email
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => __('No valid OTP found for this mobile number.')
                ];
            }

        } catch (LocalizedException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $response = [
                'success' => false,
                'message' => __('An error occurred while processing your request.')
            ];
        }

        return $resultJson->setData($response);
    }

    private function sendOtpEmail($email, $otp_code)
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
                'expire_time' => 3,
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
}
