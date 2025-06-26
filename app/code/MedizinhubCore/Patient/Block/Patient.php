<?php

namespace MedizinhubCore\Patient\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use MedizinhubCore\Patient\Model\ResourceModel\Patient\CollectionFactory as PatientCollectionFactory;

class Patient extends Template
{
    protected $customerSession;
    protected $patientCollectionFactory;
    protected $customerRepository;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        PatientCollectionFactory $patientCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->patientCollectionFactory = $patientCollectionFactory;
        parent::__construct($context, $data);
        $this->addData(['cache_lifetime' => null]);
    }
    
    /**
     * Get the current logged-in user ID
     */
    public function getCurrentUserId()
    {
        return $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomerId() : null;
    }

    /**
     * Get details of the current logged-in user
     */
    public function getCurrentUserDetails()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        try {
            $userId = $this->getCurrentUserId();
            $customer = $this->customerRepository->getById($userId);

            return [
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'email' => $customer->getEmail(),
                'mobile' => $customer->getCustomAttribute('mobile_number') 
                    ? $customer->getCustomAttribute('mobile_number')->getValue() 
                    : 'Not Provided',
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUserDetailsJson()
    {
        $userDetails = $this->getCurrentUserDetails();
        
        if ($userDetails && isset($userDetails['name'])) {
            $userDetails['name'] = ucwords(strtolower($userDetails['name']));
        }
        
        return $userDetails ? json_encode($userDetails) : '{}';
    }

    public function getPatientData()
    {
        $customerId = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomerId() : "0";

        $collection = $this->patientCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('id', 'DESC');

        return $collection;
    }
}
