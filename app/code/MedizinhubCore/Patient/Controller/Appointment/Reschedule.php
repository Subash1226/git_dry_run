<?php
namespace MedizinhubCore\Patient\Controller\Appointment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Reschedule extends Action implements CsrfAwareActionInterface
{
    protected $jsonFactory;
    protected $resourceConnection;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        try {
            // Get parameters from AJAX request
            $appointmentId = (int)$this->getRequest()->getParam('appointment_id');
            $date = $this->getRequest()->getParam('date');
            $timeSlotId = $this->getRequest()->getParam('time_slot');
            
            // Validate required parameters
            if (!$appointmentId || !$date || !$timeSlotId) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Missing required parameters.')
                ]);
            }
            
            // Get database connection
            $connection = $this->resourceConnection->getConnection();
            
            // Get appointment table name
            $appointmentTable = $this->resourceConnection->getTableName('patient_appointment');
            
            // Check if appointment exists
            $appointmentExists = $connection->fetchOne(
                "SELECT COUNT(*) FROM {$appointmentTable} WHERE appointment_id = ?",
                [$appointmentId]
            );
            
            if (!$appointmentExists) {
                return $result->setData([
                    'success' => false,
                    'message' => __('The appointment does not exist.')
                ]);
            }
            
            // Update appointment
            $affected = $connection->update(
                $appointmentTable,
                [
                    'date' => $date,
                    'time_slot' => $timeSlotId,
                    'is_rescheduled' => 1
                ],
                ['appointment_id = ?' => $appointmentId]
            );
            
            if ($affected) {
                return $result->setData([
                    'success' => true,
                    'message' => __('Appointment has been successfully rescheduled.')
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => __('Failed to update appointment. No changes were made.')
                ]);
            }
            
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}