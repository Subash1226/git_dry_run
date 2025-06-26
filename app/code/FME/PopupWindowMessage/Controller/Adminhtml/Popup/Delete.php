<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_PopupWindowMessage
 * @author    Dara Baig  (support@fmeextensions.com)
 * @copyright Copyright (c) 2018 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */
namespace FME\PopupWindowMessage\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    
    const ADMIN_RESOURCE = 'FME_PopupWindowMessage::popup_delete';
    
    protected $model;
    public function __construct(
        Action\Context $context,
        \FME\PopupWindowMessage\Model\Popup $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('pwm_id');
        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getJobsTitle();
                $this->model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The job has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_jobpage_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_jobpage_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['jobs_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a job to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
