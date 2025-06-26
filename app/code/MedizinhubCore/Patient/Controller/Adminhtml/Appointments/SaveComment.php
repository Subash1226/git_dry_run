<?php

namespace MedizinhubCore\Patient\Controller\Adminhtml\Appointments;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class SaveComment extends Action
{
    protected $uploaderFactory;
    protected $resultJsonFactory;
    protected $resourceConnection;
    protected $directoryList;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        DirectoryList $directoryList,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList = $directoryList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = ['success' => false, 'message' => ''];

        try {
            $appointmentId = $this->getRequest()->getParam('appointment_id');
            $comments = $this->getRequest()->getParam('comment');
            $prescriptionFile = $this->getRequest()->getFiles('doctor_prescription');

            if (!$appointmentId || !$comments) {
                throw new \Exception('Invalid appointment data.');
            }

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('doctor_comment');

            $data = [
                'appointment_id' => $appointmentId,
                'comment' => $comments,
            ];

            if ($prescriptionFile && $prescriptionFile['name']) {
                $uploader = $this->uploaderFactory->create(['fileId' => 'doctor_prescription']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $mediaDirectory = $this->directoryList->getPath(DirectoryList::MEDIA) . '/Doctor_Prescriptions/';
                $result = $uploader->save($mediaDirectory);

                if ($result['file']) {
                    $data['doctor_prescription'] = 'Doctor_Prescriptions/' . $result['file'];
                }
            }

            $existingComment = $connection->fetchRow(
                $connection->select()->from($tableName)->where('appointment_id = ?', $appointmentId)
            );

            if ($existingComment) {
                $connection->update($tableName, $data, ['appointment_id = ?' => $appointmentId]);
            } else {
                $connection->insert($tableName, $data);
            }

            $response['success'] = true;
            $response['message'] = 'Details saved successfully!';
        } catch (\Exception $e) {
            $response['message'] = 'An error occurred while saving the details: ' . $e->getMessage();
        }

        return $resultJson->setData($response);
    }
}
