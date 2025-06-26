<?php
namespace MedizinhubCore\Patient\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Appointment
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
        $table = $this->resourceConnection->getTableName('patient_appointment');

        $select = $connection->select()
            ->from($table)
            ->where('patient_id = ?', $customerId);

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
        $table = $this->resourceConnection->getTableName('patient_appointment');

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
