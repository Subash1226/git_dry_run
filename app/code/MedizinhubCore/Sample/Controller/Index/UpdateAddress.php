<?php

namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Action;

class UpdateAddress extends Action
{
    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $customerSession;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if (!$this->getRequest()->isPost()) {
            $result['message'] = 'Invalid request.';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $result['message'] = 'Customer not logged in';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $data = $this->getRequest()->getPostValue();

        // Check for patient ID in the request data
        if (!isset($data['address_id']) || empty($data['address_id'])) {
            $result['message'] = 'Patient ID is required for updating.';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $patientId = $data['address_id'];

        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('patient');

            $updateData = [
                'name' => isset($data['customer_name']) ? $data['customer_name'] : '',
                'age' => isset($data['age']) ? $data['age'] : null,
                'email' => isset($data['email']) ? $data['email'] : '',
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'phone' => isset($data['telephone']) ? $data['telephone'] : '',
                'whatsapp' => isset($data['whatsapp']) ? $data['whatsapp'] : null,
                'house_no' => isset($data['street'][0]) ? $data['street'][0] : '',
                'street' => isset($data['street'][1]) ? $data['street'][1] : '',
                'area' => isset($data['area']) ? $data['area'] : '',
                'city' => isset($data['city']) ? $data['city'] : '',
                'region_id' => isset($data['region_id']) ? $data['region_id'] : '',
                'postcode' => isset($data['postcode']) ? $data['postcode'] : '',
                'country_id' => 'IN', // Assuming country_id is always 'IN'
            ];

            $where = ['id = ?' => $patientId, 'customer_id = ?' => $customerId];
            $affectedRows = $connection->update($tableName, $updateData, $where);            
                $result['success'] = true;
                $result['message'] = __('Patient information successfully updated.');
        } catch (\Exception $e) {
            $result['message'] = __('Something went wrong while updating the patient information.');
            $this->logger->error($e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
