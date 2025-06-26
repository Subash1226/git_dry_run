<?php

namespace Cinovic\Otplogin\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use PlaceOrder\Whatsapp\Helper\Data as WhatsappHelper;

class CustomerUpdate extends Action
{
    protected $resultJsonFactory;
    protected $customerRepository;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $logger;
    protected $sessionManager;
    protected $curl;
    protected $emailValidator;
    protected $scopeConfig;
    protected $_customerFactory;
    protected $customerSession;
    private $resourceConnection;
    protected $whatsappHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        TransportBuilder $transportBuilder,
        CustomerFactory $customerFactory,
        CustomerSession $customerSession,
        ResourceConnection $resourceConnection,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        SessionManagerInterface $sessionManager,
        Curl $curl,
        EmailAddress $emailValidator,
        WhatsappHelper $whatsappHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->transportBuilder = $transportBuilder;
        $this->_customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->sessionManager = $sessionManager;
        $this->curl = $curl;
        $this->emailValidator = $emailValidator;
        $this->scopeConfig = $scopeConfig;
        $this->whatsappHelper = $whatsappHelper;
        parent::__construct($context);
    }

    /**
     * Send email notification
     *
     * @param array $customerData
     * @return void
     */
    protected function sendEmail($customerData)
    {
        try {
            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $templateVars = [
                'customer_name' => $customerData['firstname'] . ' ' . $customerData['lastname'],
                'customer_email' => $customerData['email']
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('customer_update_email_template') // Email template ID from your module's email_templates.xml
                ->setTemplateOptions([
                    'area' => 'frontend',
                    'store' => $storeId
                ])
                ->setTemplateVars($templateVars)
                ->setFrom([
                    'email' => 'no-reply@medizinhub.com',
                    'name' => 'MedizinHub'
                ])
                ->addTo($customerData['email'], $customerData['firstname'] . ' ' . $customerData['lastname'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            error_log("Failed to send customer update email: " . $e->getMessage());
        }
    }

    /**
     * Call WhatsApp API to send message to customer
     *
     * @param string $mobileNumber
     * @param string $userName
     * @return void
     */
    protected function callWhatsappApi($mobileNumber, $userName)
    {
        if (!$this->whatsappHelper->isEnabled()) {
            return;
        }

        $customerPayload = [
            'apiKey' => $this->whatsappHelper->getApiKey(),
            'campaignName' => 'customer_reg_image',
            'destination' => $mobileNumber,
            'userName' => $userName,
            'templateParams' => [$userName],
            'media' => [
                'url' => 'https://images.medizinhub.com/media/salesrule/images/w/e/welcome_message.jpg',
                'filename' => 'sample_media'
            ]
        ];

        $this->sendWhatsAppMessage($customerPayload);

        foreach ($this->whatsappHelper->getStaffNumbers() as $staffNumber) {
            $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));
            $utcTime->setTimezone(new \DateTimeZone('Asia/Kolkata'));
            
            $staffPayload = [
                'apiKey' => $this->whatsappHelper->getApiKey(),
                'campaignName' => 'customer_alert_new',
                'destination' => trim($staffNumber),
                'userName' => 'Staff',
                'templateParams' => [
                    $userName,
                    $mobileNumber,
                    $utcTime->format('d-m-Y H:i')
                ]
            ];

            $this->sendWhatsAppMessage($staffPayload);
        }
    }

    /**
     * Send WhatsApp message using API
     *
     * @param array $payload
     * @return void
     */
    private function sendWhatsAppMessage($payload)
    {
        try {
            $url = 'https://backend.aisensy.com/campaign/t1/api/v2';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                throw new \Exception('Failed to send WhatsApp message. Response: ' . $response);
            }

            curl_close($ch);
            $this->logger->info('WhatsApp API response: ' . $response);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send WhatsApp message: ' . $e->getMessage());
        }
    }
    
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
        $this->sessionManager->unsEmail();
        $this->sessionManager->unsCustomerName();
    }

    /**
     * Execute customer update action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $requestData = $this->getRequest()->getContent();
        $data = json_decode($requestData, true);

        if (!isset($data['customer_id'], $data['email'], $data['firstname'], $data['lastname'])) {
            return $result->setData([
                'success' => false,
                'message' => 'Missing required parameters.'
            ]);
        }

        try {
            $mobileNumber = $this->sessionManager->getMobileNumber();
            $customerId = $this->getCustomerIdByMobileNumber($mobileNumber);
            $customer = $this->customerRepository->getById($customerId);
            $firstName = ucfirst(strtolower($data['firstname']));
            $lastName = ucfirst(strtolower($data['lastname']));
            $customer->setEmail($data['email']);
            $customer->setFirstname($firstName);
            $customer->setLastname($lastName);
            $this->customerRepository->save($customer);
            $fullname = $firstName . " " . $lastName;

            $this->sendEmail([
                'email' => $data['email'],
                'firstname' => $firstName,
                'lastname' => $lastName
            ]);
            $this->processCustomerLogin($data['email']);
            $this->callWhatsappApi($mobileNumber, $fullname);

            return $result->setData([
                'success' => true,
                'message' => 'Customer information updated successfully.'
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
}
