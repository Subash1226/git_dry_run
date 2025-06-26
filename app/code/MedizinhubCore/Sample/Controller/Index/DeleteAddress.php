<?php

namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class DeleteAddress extends Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $resourceConnection;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $this->getObjectManager()->get(Session::class);
        $this->resourceConnection = $this->getObjectManager()->get(ResourceConnection::class);
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $addressId = $this->getRequest()->getParam('id');

            try {
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('patient');

                // Check if the address belongs to the customer
                $select = $connection->select()
                    ->from($tableName, ['customer_id'])
                    ->where('id = ?', $addressId);

                $address = $connection->fetchRow($select);

                if (!$address) {
                    $result['message'] = 'Address does not exist';
                } elseif ($address['customer_id'] != $customerId) {
                    $result['message'] = 'Unauthorized access';
                } else {
                    // Perform the deletion
                    $where = ['id = ?' => $addressId];
                    $affectedRows = $connection->delete($tableName, $where);

                    if ($affectedRows > 0) {
                        $result['success'] = true;
                        $result['message'] = 'Address deleted successfully';
                    } else {
                        $result['message'] = 'No records were deleted. Please check the Address ID.';
                    }
                }
            } catch (\Exception $e) {
                $result['message'] = 'Something went wrong while deleting the address.';
                $this->getObjectManager()->get(\Psr\Log\LoggerInterface::class)->error($e->getMessage());
            }
        } else {
            $result['message'] = 'User not logged in';
        }

        $response = $this->resultJsonFactory->create();
        return $response->setData($result);
    }

    protected function getObjectManager()
    {
        return ObjectManager::getInstance();
    }
}
