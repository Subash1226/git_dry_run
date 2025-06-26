<?php
namespace MedizinhubCore\Patient\Controller\Appointment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GetAvailableTimeSlots extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            // Get the date parameter from the request
            $date = $this->getRequest()->getParam('date');
            
            // Debug log the incoming request
            $this->logger->debug('GetAvailableTimeSlots - Date: ' . $date);
            
            // Validate the date parameter
            if (empty($date)) {
                return $resultJson->setData([
                    'success' => false,
                    'data' => ['error' => 'Date parameter is missing']
                ]);
            }
            
            // Use direct database queries for maximum compatibility
            $connection = $this->resource->getConnection();
            
            // Get the table names with prefixes
            $timeSlotTable = $this->resource->getTableName('time_slots');
            $appointmentsTable = $this->resource->getTableName('patient_appointment');
            
            // Debug log table names
            $this->logger->debug("Time Slot Table: $timeSlotTable, Appointments Table: $appointmentsTable");
            
            // Verify tables exist
            if (!$connection->isTableExists($timeSlotTable)) {
                $this->logger->error("Table does not exist: $timeSlotTable");
                return $resultJson->setData([
                    'success' => false,
                    'data' => ['error' => 'Time slots table not found']
                ]);
            }
            
            if (!$connection->isTableExists($appointmentsTable)) {
                $this->logger->error("Table does not exist: $appointmentsTable");
                return $resultJson->setData([
                    'success' => false,
                    'data' => ['error' => 'Appointments table not found']
                ]);
            }
            
            // Get all available time slots
            $select = $connection->select()
                ->from(
                    ['ts' => $timeSlotTable],
                    ['id' => 'id', 'time_slot' => 'slot_time']
                );
            
            $allTimeSlots = $connection->fetchAll($select);
            
            // Get booked appointments for the selected date
            $bookedSlotsSelect = $connection->select()
                ->from(
                    ['a' => $appointmentsTable],
                    ['time_slot']
                )
                ->where('date = ?', $date);
            
            $bookedSlots = $connection->fetchCol($bookedSlotsSelect);
            
            // Debug log
            $this->logger->debug('All Time Slots: ' . count($allTimeSlots));
            $this->logger->debug('Booked Slots: ' . count($bookedSlots) . ' - ' . implode(', ', $bookedSlots));
            
            // Filter out booked slots
            $availableSlots = [];
            foreach ($allTimeSlots as $slot) {
                if (!in_array($slot['id'], $bookedSlots)) {
                    $availableSlots[] = [
                        'id' => $slot['id'],
                        'time_slot' => $slot['time_slot']
                    ];
                }
            }
            
            $this->logger->debug('Available Slots: ' . count($availableSlots));
            
            // Return the result
            return $resultJson->setData([
                'success' => true,
                'data' => ['available_slots' => $availableSlots]
            ]);
            
        } catch (\Exception $e) {
            // Log the full exception
            $this->logger->error('Exception in GetAvailableTimeSlots: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
            
            // During development, return the actual error
            return $resultJson->setData([
                'success' => false,
                'data' => [
                    'error' => 'Error: ' . $e->getMessage(),
                    // Remove the following line for production
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }
}