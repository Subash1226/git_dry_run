<?php

namespace MedizinhubCore\Lab\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class Delete extends Action
{
    protected $resource;
    protected $jsonFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        JsonFactory $jsonFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $testName = $this->getRequest()->getParam('test_name');

        if ($this->customerSession->isLoggedIn() && $testName) {
            $customerId = $this->customerSession->getCustomerId();
            $connection = $this->resource->getConnection();

            $connection->delete(
                'labcart',
                ['customer_id = ?' => $customerId, 'test_name = ?' => $testName]
            );

            return $result->setData(['success' => true, 'message' => 'Item removed successfully']);
        }

        return $result->setData(['success' => false, 'message' => 'Unable to remove item']);
    }
}
