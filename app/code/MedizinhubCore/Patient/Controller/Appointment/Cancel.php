<?php
namespace MedizinhubCore\Patient\Controller\Appointment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\RequestInterface;

class Cancel extends Action
{
    protected $resultJsonFactory;
    protected $resource;
    protected $request;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resource,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
        $this->request = $request;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $appointmentId = $this->request->getParam('appointment_id');
        $reason = $this->request->getParam('reason'); // Fetch reason from AJAX request

        if (!$appointmentId || empty($reason)) {
            return $resultJson->setData([
                'success' => false,
                'message' => 'Invalid request. Appointment ID and reason are required.'
            ]);
        }

        try {
            $connection = $this->resource->getConnection();
            $tableName = $connection->getTableName('patient_appointment');

            // Update appointment status to 3 and set cancellation reason
            $connection->update(
                $tableName,
                ['appointment_status' => 4, 'cancellation_reason' => $reason], 
                ['appointment_id = ?' => $appointmentId]
            );

            return $resultJson->setData(['success' => true, 'message' => 'Appointment cancelled successfully.']);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
