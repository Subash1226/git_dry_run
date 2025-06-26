<?php
namespace MedizinhubCore\Lab\Controller\Labcart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Session as CustomerSession;

class GetTests extends Action
{
    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('Customer is not logged in.')
            ]);
        }

        try {
            $customerId = $this->customerSession->getCustomerId();
            $connection = $this->resourceConnection->getConnection();

            $select = $connection->select()
                ->from(['lc' => 'labcart'], ['test_name'])
                ->where('lc.customer_id = ?', $customerId)
                ->where('lc.status = ?', 1);

            $testNames = $connection->fetchCol($select);

            return $result->setData([
                'success' => true,
                'test_names' => $testNames
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while fetching test names.'),
                'error' => $e->getMessage()
            ]);
        }
    }
}
