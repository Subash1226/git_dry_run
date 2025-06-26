<?php

namespace MedizinhubCore\Patient\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use MedizinhubCore\Patient\Model\PatientAppointmentFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SaveStatus extends Action
{
    protected $jsonFactory;
    protected $request;
    protected $appointmentFactory;
    protected $logger;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RequestInterface $request,
        PatientAppointmentFactory $appointmentFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->appointmentFactory = $appointmentFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();

        try {
            $params = $this->getRequest()->getPostValue();
            $appointmentId = $params['appointment_id'] ?? null;
            $orderId = $params['order_id'] ?? null;
            $status = $params['status'] ?? null;

            $this->logger->info('Received parameters: Appointment ID - ' . $appointmentId . ', Order ID - ' . $orderId . ', Status - ' . $status);

            if (!$appointmentId || !$orderId || !$status) {
                return $resultJson->setData(['success' => false, 'message' => 'Missing parameters']);
            }

            // Load by Collection Filter to ensure correct record
            $appointment = $this->appointmentFactory->create()->getCollection()
                ->addFieldToFilter('appointment_id', $appointmentId)
                ->getFirstItem();

            if (!$appointment->getId()) {
                $this->logger->error('Appointment not found: ' . $appointmentId);
                return $resultJson->setData(['success' => false, 'message' => 'Appointment not found']);
            }

            // Update the order_id and payment_status
            $appointment->setData('order_id', $orderId);
            $appointment->setData('payment_status', $status);
            $appointment->setUpdatedAt(date('Y-m-d H:i:s'));

            $appointment->save();

            $this->logger->info('Payment status updated successfully for Appointment ID: ' . $appointmentId);
            return $resultJson->setData(['success' => true, 'message' => 'Order ID and payment status updated successfully']);

        } catch (LocalizedException $e) {
            $this->logger->error('Payment status save error: ' . $e->getMessage());
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->logger->error('Payment status save error: ' . $e->getMessage());
            return $resultJson->setData(['success' => false, 'message' => 'Error updating payment status']);
        }
    }
}
