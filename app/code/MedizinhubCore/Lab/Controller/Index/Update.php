<?php

namespace MedizinhubCore\Lab\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class Update extends Action
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

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('labcart');

            try {
                $connection->update(
                    $tableName,
                    ['status' => 0], // Update status to 0 for all records belonging to the customer
                    ['customer_id = ?' => $customerId]
                );

                return $result->setData(['success' => true, 'message' => 'Status updated successfully']);
            } catch (\Exception $e) {
                return $result->setData(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }

        return $result->setData(['success' => false, 'message' => 'Unable to update status']);
    }
}
