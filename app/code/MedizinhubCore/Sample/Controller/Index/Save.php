<?php
namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ResourceConnection;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $objectManager;
    protected $resultJsonFactory;
    protected $resourceConnection;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->objectManager = $objectManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = ['success' => false, 'message' => ''];

        if (!$this->getRequest()->isPost()) {
            $result['message'] = 'Invalid request.';
            return $this->resultJsonFactory->create()->setData($result);
        }

        $data = $this->getRequest()->getPostValue();

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('customer_labtest');

            $connection->insert($tableName, [
                'test_name' => $data['test_name'],
                'test_type' => $data['test_type'],
                'patient' => 1,
                'appointment_time' => $data['appointment_time'],
                'appointment_date' => $data['appointment_date'],
                'customer_id' => $data['customer_id'],
                'mobile_number' => $data['mobile_number'],
                'address_id' => $data['address_entity'],
                'payment_type' => $data['payment_type'],
                'total_price' => $data['total_price'],
                'transaction_id' => $data['transaction_id']
            ]);

            $result['success'] = true;
            $result['message'] = __('Test details have been successfully submitted.');
        } catch (\Exception $e) {
            $result['message'] = __('Something went wrong while saving the test details.');
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->error($e);
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
