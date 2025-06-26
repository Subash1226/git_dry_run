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

class AppointmentManagement
{
    protected $resourceConnection;
    protected $request;
    protected $userContext;
    protected $searchResultsFactory;
    protected $logger;

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
     * Retrieve appointment details by ID.
     *
     * @param int $id
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppointment($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $appointmentTable = $this->resourceConnection->getTableName('patient_appointment');
        
        // Use select method for appointment
        $select = $connection->select()
            ->from($appointmentTable)
            ->where('appointment_id = ?', $id);
        $result = $connection->fetchAll($select);
        
        if ($result) {
            // Use select method for time slots
            $timeSlotsTable = $this->resourceConnection->getTableName('time_slots');
            $timeSlotSelect = $connection->select()
                ->from($timeSlotsTable, ['id', 'slot_time'])
                ->where('status = ?', 1);
            $timeSlots = $connection->fetchPairs($timeSlotSelect);
            
            // Use select method for hospitals
            $hospitalsTable = $this->resourceConnection->getTableName('hospitals');
            $hospitalSelect = $connection->select()
                ->from($hospitalsTable, ['id', 'name'])
                ->where('status = ?', 1);
            $hospitals = $connection->fetchPairs($hospitalSelect);
            
            // Use select method for practitioners
            $practitionersTable = $this->resourceConnection->getTableName('practitioners');
            $practitionerSelect = $connection->select()
                ->from($practitionersTable, ['id', 'name'])
                ->where('status = ?', 1);
            $practitioners = $connection->fetchPairs($practitionerSelect);
            
            // Enhance appointment data with related information
            foreach ($result as &$appointment) {
                $appointment['time_slot'] = $timeSlots[(int)$appointment['time_slot']] ?? 'Unknown';
                $appointment['hospital_id'] = $hospitals[(int)$appointment['hospital_id']] ?? 'Unknown';
                $appointment['practitioner_id'] = $practitioners[(int)$appointment['practitioner_id']] ?? 'Unknown';

                if (isset($appointment['report_doc']) && is_string($appointment['report_doc'])) {
                    $appointment['report_doc'] = json_decode($appointment['report_doc'], true);
                }
            }
            
            // Create and return search results
            $searchResults = $this->searchResultsFactory->create();
            $searchResults->setItems($result);
            $searchResults->setTotalCount(1);
            return $searchResults;
        }

        $this->logger->warning("Appointment not found for ID: {$id}");
        throw new NoSuchEntityException(__('Appointment not found.'));
    }

    /**
     * Retrieve all appointments for the current customer.
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\AuthorizationException
     */
    public function get()
    {
        $customerId = $this->validateCustomerAccess();
        $connection = $this->resourceConnection->getConnection();
        $appointmentTable = $this->resourceConnection->getTableName('patient_appointment');
        
        // Use select method for appointments
        $select = $connection->select()
            ->from($appointmentTable)
            ->where('customer_id = ?', $customerId);
        $items = $connection->fetchAll($select);
        
        // Get patient IDs for later use
        $patientIds = array_column($items, 'patient_id');
        
        // Use select method for time slots
        $timeSlotsTable = $this->resourceConnection->getTableName('time_slots');
        $timeSlotSelect = $connection->select()
            ->from($timeSlotsTable, ['id', 'slot_time'])
            ->where('status = ?', 1);
        $timeSlots = $connection->fetchPairs($timeSlotSelect);
        
        // Use select method for hospitals
        $hospitalsTable = $this->resourceConnection->getTableName('hospitals');
        $hospitalSelect = $connection->select()
            ->from($hospitalsTable, ['id', 'name'])
            ->where('status = ?', 1);
        $hospitals = $connection->fetchPairs($hospitalSelect);
        
        // Use select method for practitioners
        $practitionersTable = $this->resourceConnection->getTableName('practitioners');
        $practitionerSelect = $connection->select()
            ->from($practitionersTable, ['id', 'name'])
            ->where('status = ?', 1);
        $practitioners = $connection->fetchPairs($practitionerSelect);
        
        // Fetch patient names if there are patient IDs
        $patientNames = [];
        if (!empty($patientIds)) {
            $patientTable = $this->resourceConnection->getTableName('patient');
            $patientSelect = $connection->select()
                ->from($patientTable, ['id', 'name'])
                ->where('id IN (?)', $patientIds);
            $patientNames = $connection->fetchPairs($patientSelect);
        }

        // Enhance appointment data with related information
        foreach ($items as &$appointment) {
            $appointment['time_slot'] = $timeSlots[(int)$appointment['time_slot']] ?? 'Unknown';
            $appointment['hospital_id'] = $hospitals[(int)$appointment['hospital_id']] ?? 'Unknown';
            $appointment['practitioner_id'] = $practitioners[(int)$appointment['practitioner_id']] ?? 'Unknown';
            $appointment['patient_name'] = $patientNames[(int)$appointment['patient_id']] ?? 'Unknown';
            
            if (isset($appointment['report_doc']) && is_string($appointment['report_doc'])) {
                $appointment['report_doc'] = json_decode($appointment['report_doc'], true);
            }
        }
        
        // Create and return search results
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));
        return $searchResults;
    }

    /**
     * Delete an appointment by ID.
     *
     * @param int $id
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete($id)
    {
        $conn = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('patient_appointment');
        $existingTest = $conn->fetchOne($conn->select()->from($table, 'appointment_id')->where('appointment_id = ?', $id));
        if (!$existingTest) {
            $this->logger->warning("Attempted to delete non-existent appointment ID: {$id}");
            throw new NoSuchEntityException(__('Appointment not found.'));
        }
        $conn->delete($table, ['appointment_id = ?' => $id]);
        $this->logger->info("Appointment ID: {$id} successfully deleted.");
        return [
            'data' => [
                'success' => true,
                'message' => 'Appointment successfully deleted.'
            ]
        ];
    }

    /**
     * Validate customer access.
     *
     * @return int
     * @throws \Magento\Framework\Exception\AuthorizationException
     */
    private function validateCustomerAccess()
    {
        $customerId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        if ($userType !== UserContextInterface::USER_TYPE_CUSTOMER) {
            $this->logger->error("Unauthorized access attempt by user ID: {$customerId}");
            throw new AuthorizationException(__('Current user is not authorized.'));
        }
        if (!$customerId) {
            $this->logger->error("Customer authentication failed.");
            throw new AuthorizationException(__('Customer not authenticated.'));
        }
        return $customerId;
    }
}
