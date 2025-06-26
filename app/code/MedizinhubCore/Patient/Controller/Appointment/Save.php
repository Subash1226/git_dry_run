<?php

namespace MedizinhubCore\Patient\Controller\Appointment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use MedizinhubCore\Patient\Model\PatientAppointmentFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Filesystem;

class Save extends Action
{
    protected $resultJsonFactory;
    protected $patientAppointmentFactory;
    protected $directoryList;
    protected $logger;
    protected $customerSession;
    protected $filesystem;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PatientAppointmentFactory $patientAppointmentFactory,
        DirectoryList $directoryList,
        LoggerInterface $logger,
        CustomerSession $customerSession,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->patientAppointmentFactory = $patientAppointmentFactory;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            // Log POST and FILES data
            $postData = $this->getRequest()->getPostValue();
            $this->logger->info('POST Data: ' . print_r($postData, true));
            $this->logger->info('FILES Array: ' . print_r($_FILES, true));
    
            // Get customer ID from session
            $customerId = $this->customerSession->getCustomerId();
            if (!$customerId) {
                $this->logger->error('Customer not logged in.');
                return $result->setData(['success' => false, 'message' => 'Customer not logged in']);
            }
            $this->logger->info('Customer ID: ' . $customerId);
    
            // Proceed with the rest of your logic
            $fileNames = isset($postData['file_names']) ? json_decode($postData['file_names'], true) : [];
            $this->logger->info('File Names Array: ' . print_r($fileNames, true));
    
            if (
                isset($postData['patient_id']) && isset($postData['practitioner_id']) && isset($postData['hospital_id']) &&
                isset($postData['appointment_date']) && isset($postData['time_slot']) && isset($postData['patient_issue'])
            ) {
                // File handling
                if (isset($_FILES['report_docs'])) {
                    // Get the media directory write interface
                    $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    
                    // Ensure the Patient_reports directory exists in the media directory
                    $mediaDirectory->create('Patient_reports');
                    $this->logger->info('Ensured Patient_reports directory exists in media directory');
    
                    $filePaths = [];
                    foreach ($_FILES['report_docs']['name'] as $key => $filename) {
                        $tmpName = $_FILES['report_docs']['tmp_name'][$key];
                        if ($_FILES['report_docs']['error'][$key] === UPLOAD_ERR_OK) {
                            // Sanitize filename
                            $safeFilename = $this->getSafeFilename($filename);
                            $relativePath = 'Patient_reports/' . $safeFilename;
    
                            // Write the file to the media directory (will use S3 if configured)
                            $mediaDirectory->writeFile($relativePath, file_get_contents($tmpName));
                            $filePaths[] = $relativePath;
                            $this->logger->info('File uploaded successfully to: ' . $relativePath);
                        } else {
                            $this->logger->error('File upload error for ' . $filename . ': ' . $_FILES['report_docs']['error'][$key]);
                        }
                    }
    
                    if (!empty($filePaths)) {
                        $reportDoc = json_encode($filePaths);
                        $this->logger->info('Final Report Docs JSON: ' . $reportDoc);
                    }
                }
    
                // Save the appointment data along with report_doc and customer_id
                $appointmentModel = $this->patientAppointmentFactory->create();
                $appointmentModel->setData('patient_id', $postData['patient_id'])
                    ->setData('practitioner_id', $postData['practitioner_id'])
                    ->setData('hospital_id', $postData['hospital_id'])
                    ->setData('date', $postData['appointment_date'])
                    ->setData('time_slot', $postData['time_slot'])
                    ->setData('patient_issue', $postData['patient_issue'])
                    ->setData('report_doc', $reportDoc ?? null)
                    ->setData('customer_id', $customerId)
                    ->setData('appointment_status', 1)
                    ->save();
    
                $this->logger->info('Appointment saved successfully.');
                return $result->setData(['success' => true]);
            } else {
                $this->logger->error('Missing required fields.');
                return $result->setData(['success' => false, 'message' => 'Missing required fields']);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error occurred: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getSafeFilename($filename)
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $filename = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $filename);
        $info = pathinfo($filename);
        $base = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
        $i = 0;
    
        $relativePath = 'Patient_reports/' . $filename;
        while ($mediaDirectory->isExist($relativePath)) {
            $i++;
            $filename = $base . '_' . $i . $ext;
            $relativePath = 'Patient_reports/' . $filename;
        }
        return $filename;
    }
}
