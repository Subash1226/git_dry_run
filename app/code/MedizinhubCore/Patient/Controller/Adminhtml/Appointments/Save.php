<?php

namespace MedizinhubCore\Patient\Controller\Adminhtml\Appointments;

use Magento\Backend\App\Action;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use MedizinhubCore\Patient\Model\AppointmentsFactory;

class Save extends Action
{
    protected $uploaderFactory;
    protected $ioFile;
    protected $directoryList;
    protected $appointmentsFactory;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        File $ioFile,
        DirectoryList $directoryList,
        AppointmentsFactory $appointmentsFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->ioFile = $ioFile;
        $this->directoryList = $directoryList;
        $this->appointmentsFactory = $appointmentsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (empty($data['patient_id'])) {
            $this->messageManager->addError(__('Patient ID is required.'));
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('appointment_id')]);
            return;
        }

        $patient = $this->_objectManager->create('MedizinhubCore\Patient\Model\Patient')->load($data['patient_id']);
        if (!$patient->getId()) {
            $this->messageManager->addError(__('Invalid Patient ID.'));
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('appointment_id')]);
            return;
        }

        if (!empty($data['date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['date']);
            if ($date && $date->format('Y-m-d') === $data['date']) {
                $currentDate = new \DateTime();
                if ($date < $currentDate->setTime(0, 0)) {
                    $this->messageManager->addError(__('The appointment date cannot be in the past.'));
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('appointment_id')]);
                    return;
                }
                $data['date'] = $date->format('Y-m-d');
            } else {
                $this->messageManager->addError(__('Invalid date format.'));
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('appointment_id')]);
                return;
            }
        }

        $appointment = $this->appointmentsFactory->create();
        $appointmentId = $this->getRequest()->getParam('appointment_id');

        if ($appointmentId) {
            $appointment->load($appointmentId);
            if (!$appointment->getId()) {
                $this->messageManager->addError(__('This appointment no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }


        $uploadedFiles = [];
        if (isset($_FILES['report_doc']) && isset($_FILES['report_doc']['name']) && is_array($_FILES['report_doc']['name'])) {
            $files = $_FILES['report_doc'];

            $reportDocData = $appointment->getData('report_doc');
            $existingFiles = !empty($reportDocData) ? json_decode($reportDocData, true) : [];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] == 0) {
                    try {
                        $uploader = $this->uploaderFactory->create(['fileId' => [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i]
                        ]]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);

                        $path = $this->directoryList->getPath(DirectoryList::MEDIA) . '/Patient_reports/';
                        $result = $uploader->save($path);

                        if ($result['file']) {
                            $uploadedFiles[] = 'Patient_reports/' . $result['file'];
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('File could not be uploaded: ' . $files['name'][$i]));
                    }
                }
            }

            $allFiles = array_merge($existingFiles, $uploadedFiles);
            $data['report_doc'] = json_encode($allFiles);
        }

        $appointment->setData($data);

        try {
            $appointment->save();
            $this->messageManager->addSuccess(__('You saved the appointment.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $appointment->getId()]);
                return;
            }
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('appointment_id')]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Patient::save');
    }
}
