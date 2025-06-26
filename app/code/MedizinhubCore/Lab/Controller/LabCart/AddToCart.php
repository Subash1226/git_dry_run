<?php

namespace MedizinhubCore\Lab\Controller\LabCart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;

class AddtoCart extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = ['success' => false, 'message' => 'Unable to process the request.'];

        try {
            $params = $this->getRequest()->getContent();
            $data = json_decode($params, true);

            if (!isset($data['labtest_name'])) {
                $response['message'] = 'Lab test name is missing.';
                return $result->setData($response);
            }

            $labTestName = $data['labtest_name'];
            $customerId = $this->customerSession->getCustomerId();

            if (!$customerId) {
                $response['message'] = 'Customer not logged in.';
                return $result->setData($response);
            }

            $objectManager = ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('labcart');

            $connection->insert($tableName, [
                'customer_id' => $customerId,
                'test_name' => $labTestName,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            $response = [
                'success' => true,
                'message' => 'Lab test added to cart successfully.',
            ];
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $result->setData($response);
    }
}
