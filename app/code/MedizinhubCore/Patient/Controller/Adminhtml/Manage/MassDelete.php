<?php

namespace MedizinhubCore\Patient\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use MedizinhubCore\Patient\Model\ManageFactory;
use Magento\Framework\Exception\LocalizedException;

class MassDelete extends Action
{
    protected $manageFactory;

    public function __construct(
        Context $context,
        ManageFactory $manageFactory
    ) {
        $this->manageFactory = $manageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('selected');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select item(s) to delete.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $patient = $this->manageFactory->create()->load($id);
                    $patient->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while deleting records.')
                );
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MedizinhubCore_Patient::delete');
    }
}
