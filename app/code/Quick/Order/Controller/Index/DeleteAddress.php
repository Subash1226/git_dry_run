<?php

namespace Quick\Order\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class DeleteAddress extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $addressRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Session $customerSession,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->addressRepository = $addressRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $addressId = $this->getRequest()->getParam('address_id');

            try {
                $address = $this->addressRepository->getById($addressId);
                if ($address->getCustomerId() == $customerId) {
                    $this->addressRepository->delete($address);
                    $result = ['success' => true, 'message' => 'Address deleted successfully'];
                } else {
                    $result['message'] = 'Unauthorized access';
                }
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            $result['message'] = 'User not logged in';
        }

        $response = $this->resultJsonFactory->create();
        return $response->setData($result);
    }
}
