<?php
namespace MedizinhubCore\Sample\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Action;

class SaveAddress extends Action
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

        try {
            $resource = $this->resourceConnection;
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('patient');

            $insertData = [
                'customer_id' => $customerId,
                'name' => isset($data['customer_name']) ? $data['customer_name'] : '',
                'age' => isset($data['age']) ? $data['age'] : null,
                'email' => isset($data['email']) ? $data['email'] : '',
                'gender' => isset($data['gender']) ? $data['gender'] : '',
                'phone' => isset($data['telephone']) ? $data['telephone'] : '',
                'whatsapp' => isset($data['whatsapp']) ? $data['whatsapp'] : null,
                'house_no' => isset($data['house_no']) ? $data['house_no'] : '',
                'street' => isset($data['street']) ? $data['street'] : '',
                'area' => isset($data['area']) ? $data['area'] : '',
                'city' => isset($data['city']) ? $data['city'] : '',
                'region_id' => isset($data['region_id']) ? $data['region_id'] : '',
                'postcode' => isset($data['postcode']) ? $data['postcode'] : '',
                'country_id' => isset($data['country_id']) ? $data['country_id'] : 'IN',
            ];

            $connection->insert($tableName, $insertData);
            $result['success'] = true;
            $result['message'] = __('Patient information successfully submitted.');
        } catch (\Exception $e) {
            $result['message'] = __('Something went wrong while saving the patient information.');
            $this->logger->error($e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
