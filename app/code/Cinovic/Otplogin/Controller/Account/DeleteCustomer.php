<?php

namespace Cinovic\Otplogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;

class DeleteCustomer extends Action
{
    protected $customerRepository;
    protected $resultJsonFactory;
    protected $logger;
    protected $registry;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->registry = $registry;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $customerId = $this->getRequest()->getParam('customer_id');

        if ($customerId) {
            try {
                // Set isSecureArea to true to allow deletion
                $this->registry->register('isSecureArea', true);

                // Load and delete the customer
                $customer = $this->customerRepository->getById($customerId);
                $this->customerRepository->delete($customer);

                // Unset the registry entry after deletion
                $this->registry->unregister('isSecureArea');

                $result->setData(['success' => true, 'message' => __('Customer deleted successfully.')]);
            } catch (\Exception $e) {
                $this->logger->error('Error deleting customer: ' . $e->getMessage());
                $result->setData(['success' => false, 'message' => __('Unable to delete customer.')]);
            }
        } else {
            $result->setData(['success' => false, 'message' => __('Invalid customer ID.')]);
        }

        return $result;
    }
}
