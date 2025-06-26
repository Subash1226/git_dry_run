<?php
namespace MedizinhubCore\Patient\Model\Appointment;

use MedizinhubCore\Patient\Api\AppointmentManagementInterface;
use MedizinhubCore\Patient\Model\PatientAppointmentFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\RequestInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Filesystem; // Add this import

class Management implements AppointmentManagementInterface
{
    protected $patientAppointmentFactory;
    protected $directoryList;
    protected $logger;
    protected $file;
    protected $request;
    protected $userContext;
    protected $filesystem; // Add this property

    private const UPLOAD_DIR = 'Patient_reports';
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    private const MAX_FILE_SIZE = 10485760; // 10MB

    public function __construct(
        PatientAppointmentFactory $patientAppointmentFactory,
        DirectoryList $directoryList,
        LoggerInterface $logger,
        File $file,
        RequestInterface $request,
        UserContextInterface $userContext,
        Filesystem $filesystem // Inject Filesystem
    ) {
        $this->patientAppointmentFactory = $patientAppointmentFactory;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->file = $file;
        $this->request = $request;
        $this->userContext = $userContext;
        $this->filesystem = $filesystem; // Initialize the property
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

    /**
     * Save appointment with multiple file uploads
     */
    public function saveAppointment(
        $patientId,
        $practitionerId,
        $hospitalId,
        $appointmentDate,
        $timeSlot,
        $patientIssue,
        $files = []
    ) {
        try {
            $customerId = $this->validateCustomerAccess();

            $uploadedFiles = $this->request->getFiles()->toArray();
            $this->logger->info('Initial files array: ' . print_r($uploadedFiles, true));

            $filePaths = $this->processUploadedFiles($uploadedFiles);

            if (isset($filePaths['error'])) {
                return ['success' => false, 'message' => $filePaths['error']];
            }

            $appointmentModel = $this->patientAppointmentFactory->create();
            $appointmentModel->setData([
                'patient_id' => $patientId,
                'customer_id' => $customerId,
                'practitioner_id' => $practitionerId,
                'hospital_id' => $hospitalId,
                'date' => trim($appointmentDate),
                'time_slot' => trim($timeSlot),
                'patient_issue' => trim($patientIssue),
                'report_doc' => json_encode($filePaths),
                'appointment_status' => 1
            ])->save();

            return [
                'status' => 'success',
                'appointment' => [
                    'appointment_id' => $appointmentModel->getId(),
                ],
                'files' => [
                    'uploaded_files' => $filePaths,
                    'count' => count($filePaths),
                ],
                'extra' => [
                    'message' => 'Files uploaded successfully',
                    'timestamp' => time(),
                ]
            ];
        } catch (AuthorizationException $e) {
            $this->logger->error('Authorization error in saveAppointment: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $this->logger->error('Error in saveAppointment: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process uploaded files
     */
    private function processUploadedFiles($uploadedFiles)
    {
        $filePaths = [];

        if (!isset($uploadedFiles['report_docs'])) {
            return $filePaths;
        }

        try {
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $mediaDirectory->create(self::UPLOAD_DIR); // Ensure the Reports directory exists in media storage
            $files = $this->normalizeFileArray($uploadedFiles['report_docs']);

            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $this->logger->error('Upload error for file: ' . $file['name'] . ' with error code: ' . $file['error']);
                    continue;
                }

                if (!$this->validateFile($file)) {
                    continue;
                }

                $safeFilename = $this->getSafeFilename($file['name']);
                $relativePath = self::UPLOAD_DIR . '/' . $safeFilename;

                // Write the file to the media directory (S3 if configured)
                $mediaDirectory->writeFile($relativePath, file_get_contents($file['tmp_name']));
                $filePaths[] = $relativePath;
                $this->logger->info('Successfully uploaded: ' . $relativePath);
            }

            return $filePaths;
        } catch (\Exception $e) {
            $this->logger->error('Error processing files: ' . $e->getMessage());
            return ['error' => 'Error processing uploaded files'];
        }
    }

    /**
     * Normalize file array structure
     */
    private function normalizeFileArray($fileInput)
    {
        $normalized = [];

        if (isset($fileInput[0])) {
            return $fileInput;
        }

        if (!is_array($fileInput['name'])) {
            $normalized[] = $fileInput;
        } else {
            foreach ($fileInput['name'] as $key => $name) {
                $normalized[] = [
                    'name' => $fileInput['name'][$key],
                    'type' => $fileInput['type'][$key],
                    'tmp_name' => $fileInput['tmp_name'][$key],
                    'error' => $fileInput['error'][$key],
                    'size' => $fileInput['size'][$key]
                ];
            }
        }

        return $normalized;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file)
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $this->logger->error('Invalid file extension: ' . $extension);
            return false;
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->logger->error('File size exceeds limit: ' . $file['size']);
            return false;
        }

        if (!in_array($file['type'], [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            $this->logger->error('Invalid file type: ' . $file['type']);
            return false;
        }

        return true;
    }

    /**
     * Get upload directory (not used directly for writing but kept for consistency)
     */
    private function getUploadDirectory()
    {
        try {
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $uploadDir = self::UPLOAD_DIR;

            if (!$mediaDirectory->isExist($uploadDir)) {
                $mediaDirectory->create($uploadDir);
                $this->logger->info('Created upload directory: ' . $uploadDir);
            }

            return $mediaDirectory->getAbsolutePath($uploadDir);
        } catch (\Exception $e) {
            $this->logger->error('Error creating upload directory: ' . $e->getMessage());
            throw new FileSystemException(__('Failed to create upload directory'));
        }
    }

    /**
     * Generate safe filename
     */
    private function getSafeFilename($filename)
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $filename = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $filename);
        $info = pathinfo($filename);
        $base = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
        $timestamp = date('YmdHis');
        $newFilename = $base . '_' . $timestamp . $ext;
        $counter = 0;
        $relativePath = self::UPLOAD_DIR . '/' . $newFilename;

        while ($mediaDirectory->isExist($relativePath)) {
            $counter++;
            $newFilename = $base . '_' . $timestamp . '_' . $counter . $ext;
            $relativePath = self::UPLOAD_DIR . '/' . $newFilename;
        }

        return $newFilename;
    }
}