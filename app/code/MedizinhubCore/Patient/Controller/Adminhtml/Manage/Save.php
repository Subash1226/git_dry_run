<?php

namespace MedizinhubCore\Patient\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use MedizinhubCore\Patient\Model\ManageFactory;

class Save extends Action
{
    protected $pageFactory;
    protected $manageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ManageFactory $manageFactory,
    ) {
        $this->pageFactory = $pageFactory;
        $this->manageFactory = $manageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('*/*/');
            return;
        }

        try {
            $model = $this->manageFactory->create();
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This Patient no longer exists.'));
                    $this->_redirect('*/*/');
                    return;
                }
            }

            $model->setData($data);
            $model->save();

            $this->messageManager->addSuccessMessage(__('You saved the Patient.'));
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }

        // Handle the date field
        if (!empty($data['date_of_birth'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['date_of_birth']);
            if ($date && $date->format('Y-m-d') === $data['date_of_birth']) {
                // Ensure the date is not in the future
                $currentDate = new \DateTime();
                if ($date > $currentDate->setTime(0, 0)) { // Compare without considering time
                    $this->messageManager->addError(__('The Born Date cannot be in the future.'));
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                    return;
                }
                $data['date_of_birth'] = $date->format('Y-m-d'); // Ensure the date is stored in the correct format
            } else {
                $this->messageManager->addError(__('Invalid date format.'));
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Patient::save');
    }
}
