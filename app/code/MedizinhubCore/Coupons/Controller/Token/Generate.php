<?php
namespace MedizinhubCore\Coupons\Controller\Token;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\User\Model\UserFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Generate extends Action implements CsrfAwareActionInterface
{
    protected $jsonFactory;
    protected $tokenFactory;
    protected $customerFactory;
    protected $userFactory;
    protected $customerSession;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        TokenFactory $tokenFactory,
        CustomerFactory $customerFactory,
        UserFactory $userFactory,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->tokenFactory = $tokenFactory;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $result->setHeader('Content-Type', 'application/json');
        
        try {
            $type = $this->getRequest()->getParam('type', 'customer');

            $response = [
                'success' => true,
                'type' => $type
            ];

            if ($type === 'customer') {
                $response['customerToken'] = $this->generateCustomerToken();
            } else if ($type === 'admin') {
                $response['adminToken'] = $this->generateAdminToken();
            } else {
                throw new \Exception('Invalid token type requested');
            }

            return $result->setData($response);

        } catch (\Exception $e) {
            $this->logger->error("Error generating token", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function generateCustomerToken()
    {
        if (!$this->customerSession->isLoggedIn()) {
            throw new \Exception('Customer not logged in');
        }

        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        
        if (!$customer->getId()) {
            throw new \Exception('Customer not found');
        }

        $token = $this->tokenFactory->create();
        return $token->createCustomerToken($customer->getId())->getToken();
    }
}