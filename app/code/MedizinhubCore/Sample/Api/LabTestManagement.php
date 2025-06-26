<?php
namespace MedizinhubCore\Sample\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class LabTestManagement
{
    protected $resourceConnection;
    protected $request;
    protected $userContext;
    protected $searchResultsFactory;
    protected $logger;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param Request $request
     * @param UserContextInterface $userContext
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Request $request,
        UserContextInterface $userContext,
        SearchResultsInterfaceFactory $searchResultsFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
        $this->userContext = $userContext;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Create new lab test (patient)
     *
     * @param mixed $testData
     * @return array
     */
    public function create($testData)
    {
        try {
            $this->logger->info('Lab Test Data:', $testData);

            $customerId = $this->validateCustomerAccess();
            $testData['customer_id'] = $customerId;
            $connection = $this->resourceConnection->getConnection();
            $labTestTable = $this->resourceConnection->getTableName('customer_labtest');
            $customerExists = $connection->fetchOne(
                $connection->select()->from('customer_entity', ['entity_id'])->where('entity_id = ?', $testData['customer_id'])
            );
            if (!$customerExists) {
                throw new \Exception("Invalid customer_id: {$testData['customer_id']}. Customer does not exist.");
            }
            $connection->insert($labTestTable, $testData);
            $newId = $connection->lastInsertId($labTestTable);
            if ($newId) {
                return [
                   'data' => [
                        'success' => true,
                        'message' => 'Lab test successfully created.'
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create lab test.'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error in Lab Test creation: ' . $e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get lab test by ID
     *
     * @param int $id
     * @return SearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getLabTest($id)
    {
        $conn = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('customer_labtest');
        $patientTable = $this->resourceConnection->getTableName('patient');

        // Fetch lab test data along with address_id
        $select = $conn->select()
            ->from($table)
            ->where('test_id = ?', $id);
        $result = $conn->fetchRow($select);

        if ($result) {
            // Fetch patient name based on address_id
            if (!empty($result['address_id'])) {
                $addressSelect = $conn->select()
                    ->from($patientTable, ['name'])
                    ->where('id = ?', $result['address_id']);
                $patientName = $conn->fetchOne($addressSelect);
                $result['patient_name'] = $patientName ?: 'N/A';
            } else {
                $result['patient_name'] = 'N/A';
            }

            /** @var SearchResultsInterface $searchResults */
            $searchResults = $this->searchResultsFactory->create();
            $searchResults->setItems([$result]);
            $searchResults->setTotalCount(1);
            return $searchResults;
        }

        throw new NoSuchEntityException(__('Lab test not found.'));
    }

    /**
     * Get list of all lab tests for a customer
     *
     * @return SearchResultsInterface
     */
    public function get()
    {
        $customerId = $this->validateCustomerAccess();

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('customer_labtest');
        $patientTable = $this->resourceConnection->getTableName('patient');

        // Fetch lab tests along with address_id
        $select = $connection->select()
            ->from($table)
            ->where('customer_id = ?', $customerId);
        $items = $connection->fetchAll($select);

        // Fetch patient names for each address_id
        foreach ($items as &$item) {
            if (!empty($item['address_id'])) {
                $addressSelect = $connection->select()
                    ->from($patientTable, ['name'])
                    ->where('id = ?', $item['address_id']);
                $patientName = $connection->fetchOne($addressSelect);
                $item['patient_name'] = $patientName ?: 'N/A';
            } else {
                $item['patient_name'] = 'N/A';
            }
        }

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));

        return $searchResults;
    }



 /**
 * Update lab test details
 *
 * @param int $id
 * @param array $testData
 * @return mixed
 * @throws LocalizedException
 */
public function update($id, $testData)
{
    try {
        $conn = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('customer_labtest');

        $conn->update($table, $testData, ['test_id = ?' => $id]);

        return [
            'success' => true,
            'message' => 'Lab test successfully updated.'
        ];
    } catch (\Exception $e) {
        $this->logger->error('Error updating Lab Test: ' . $e->getMessage());
        throw new LocalizedException(__($e->getMessage()));
    }
}
/**
 * Delete a lab test
 *
 * @param int $id Lab test ID
 * @return array
 * @throws \Magento\Framework\Exception\LocalizedException
 */
public function delete($id)
{
    $conn = $this->resourceConnection->getConnection();
    $table = $this->resourceConnection->getTableName('customer_labtest');
    $existingTest = $conn->fetchOne($conn->select()->from($table, 'test_id')->where('test_id = ?', $id));
    if (!$existingTest) {
        throw new \Magento\Framework\Exception\NoSuchEntityException(__('Lab test not found.'));
    }
    $conn->delete($table, ['test_id = ?' => $id]);
    return [
        'data' => [
            'success' => true,
            'message' => 'Lab test successfully deleted.'
        ]
    ];
}


    /**
     * Validate customer access
     *
     * @throws AuthorizationException
     * @return int
     */
    private function validateCustomerAccess()
    {
        $customerId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        if ($userType !== UserContextInterface::USER_TYPE_CUSTOMER) {
            throw new AuthorizationException(__('Current user is not authorized.'));
        }

        if (!$customerId) {
            throw new AuthorizationException(__('Customer not authenticated.'));
        }

        return $customerId;
    }
}
