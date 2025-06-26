<?php

namespace Quick\Order\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\AddressFactory;

class SaveAddress extends Action
{
    protected $resultJsonFactory;
    protected $addressFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressFactory $addressFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getPostValue();
        try {
            $customerId = $this->customerSession->getCustomer()->getId();
            if (!$customerId) {
                throw new \Exception('Customer not logged in');
            }

            $address = $this->addressFactory->create();
            $address->setCustomerId($customerId)
                ->setFirstname($postData['customer_name'])
                ->setLastname($postData['last_name'])
                ->setStreet($postData['street'])
                ->setCity($postData['city'])
                ->setCompany($postData['company'])
                ->setRegion($postData['region'])
                ->setPostcode($postData['postcode'])
                ->setTelephone($postData['telephone'])
                ->setCountryId($postData['country_id'])
                ->setRegionId($postData['region_id'])
                // ->setIsDefaultBilling(true)
                // ->setIsDefaultShipping(true)
                ->save();

            $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        return $result;
    }
}
