<?php

namespace MedizinhubCore\Patient\Api;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class AppointmentInterface
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
     * Get list of all hospitals (Admin Access Required)
     *
     * @return SearchResultsInterface
     * @throws AuthorizationException|NoSuchEntityException
     */
    public function getHospitals()
    {
        $adminId = $this->validateAdminAccess();
        $conn = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('hospitals');

        $select = $conn->select()->from($table)->where('status = ?', 1);
        $result = $conn->fetchAll($select);

        if ($result) {
            $searchResults = $this->searchResultsFactory->create();
            $searchResults->setItems($result);
            $searchResults->setTotalCount(count($result));
            return $searchResults;
        }

        throw new NoSuchEntityException(__('Hospitals not found.'));
    }

    /**
     * Get list of all practitioners (Admin Access Required)
     *
     * @return SearchResultsInterface
     * @throws AuthorizationException|NoSuchEntityException
     */
    public function getPractioners()
    {
        $adminId = $this->validateAdminAccess();
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('practitioners');

        $select = $connection->select()->from($table)->where('status = ?', 1);
        $items = $connection->fetchAll($select);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));

        return $searchResults;
    }

    /**
     * Get list of all time slots (Admin Access Required)
     *
     * @return SearchResultsInterface
     * @throws AuthorizationException|NoSuchEntityException
     */
    public function getTimeslots()
    {
        $adminId = $this->validateAdminAccess(); // Ensure Admin Access
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('time_slots');

        $select = $connection->select()->from($table)->where('status = ?', 1);
        $items = $connection->fetchAll($select);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));

        return $searchResults;
    }

    /**
     * Validate Admin Access
     *
     * @throws AuthorizationException
     * @return int
     */
    private function validateAdminAccess()
    {
        $adminId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();
        if (!$adminId) {
            throw new AuthorizationException(__('Admin token is required.'));
        }
        if ($userType !== UserContextInterface::USER_TYPE_ADMIN) {
            throw new AuthorizationException(__('Access denied. Only admin users can perform this action.'));
        }
        return $adminId;
    }

}
