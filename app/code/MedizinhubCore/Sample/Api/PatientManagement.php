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

class PatientManagement
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
     * Get list of all patients
     *
     * @return SearchResultsInterface
     */
    public function getList()
    {
        $customerId = $this->validateCustomerAccess();

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('patient');

        $select = $connection->select()
            ->from($table)
            ->where('customer_id = ?', $customerId);

        $items = $connection->fetchAll($select);

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));

        return $searchResults;
    }

    /**
     * Get patient by ID
     *
     * @param int $id
     * @return SearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $conn = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('patient');

        $select = $conn->select()
            ->from($table)
            ->where('id = ?', $id);

        $result = $conn->fetchRow($select);

        if (!$result) {
            throw new NoSuchEntityException(
                __('Patient with id "%1" does not exist.', $id)
            );
        }

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems([$result]);
        $searchResults->setTotalCount(1);

        return $searchResults;
    }



/**
 * Create new patient
 *
 * @param mixed $patientData
 * @return array
 */
public function create($patientData)
{
    try {
        $customerId = $this->validateCustomerAccess();
        $patientData['customer_id'] = $customerId;

        $connection = $this->resourceConnection->getConnection();
        $customerTable = $this->resourceConnection->getTableName('customer_entity');
        $patientTable = $this->resourceConnection->getTableName('patient');

        $customerExists = $connection->fetchOne(
            $connection->select()->from($customerTable, ['entity_id'])->where('entity_id = ?', $patientData['customer_id'])
        );

        if (!$customerExists) {
            throw new \Exception("Invalid customer_id: {$patientData['customer_id']}. Customer does not exist.");
        }

        $connection->insert($patientTable, $patientData);
        $newId = $connection->lastInsertId($patientTable);
        if ($newId) {
            return [
               'data' => [
                    'success' => true,
                    'message' => 'Patient successfully created.'
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create patient.'
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}


 /**
 * Update patient
 *
 * @param int $id
 * @param mixed $patientData
 * @return array
 * @throws LocalizedException
 */
public function update($id, $patientData)
{
    try {
        $this->validateCustomerAccess();
        $existingPatient = $this->getById($id);

        if (!$existingPatient) {
            return [
                'data' => [
                    'success' => false,
                    'message' => 'Patient not found.'
                ]
            ];
        }

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('patient');

        $connection->beginTransaction();
        try {
            $connection->update($table, $patientData, ['id = ?' => $id]);
            $connection->commit();

            return [
                'data' => [
                    'success' => true,
                    'message' => 'Patient successfully updated.'
                ]
            ];
        } catch (\Exception $e) {
            $connection->rollBack();
            return [
                'data' => [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]
            ];
        }
    } catch (\Exception $e) {
        return [
            'data' => [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]
        ];
    }
}


    /**
     * Delete patient
     *
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function delete($id)
    {
        try {
            $this->validateCustomerAccess();

            // Verify patient exists before deletion
            $this->getById($id);

            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('patient');

            $connection->beginTransaction();
            try {
                $result = $connection->delete($table, ['id = ?' => $id]);
                $connection->commit();
                return (bool)$result;
            } catch (\Exception $e) {
                $connection->rollBack();
                throw new LocalizedException(__('Could not delete patient: %1', $e->getMessage()));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not delete patient: %1', $e->getMessage()));
        }
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
