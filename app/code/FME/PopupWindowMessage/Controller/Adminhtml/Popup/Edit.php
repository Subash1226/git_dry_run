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

class Edit extends \Magento\Backend\App\Action
{

    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $ruleModel;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \FME\PopupWindowMessage\Model\Popup $ruleModel
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->ruleModel = $ruleModel;
        
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_PopupWindowMessage::save');
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('FME_PopupWindowMessage::popupwindowmessage_popup')
            ->addBreadcrumb(__('FME'), __('FME'))
            ->addBreadcrumb(__('Manage Popup'), __('Manage Popup'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()
            ->getParam('id');
        $model = $this->ruleModel;
        if ($id) {
            $model->load($id);
            
            if (!$model->getId()) {
                $this->messageManager->addError(__('This record no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setFormName('popupwindowmessage_popup_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );
        $this->_coreRegistry->register('percentage_pricing_data', $model);
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Popup') : __('New Popup'),
            $id ? __('Edit Popup') : __('New Popup')
        );
        
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(__('Popups'));
        $resultPage->getConfig()
            ->getTitle()
            ->prepend($model->getPwmId() ? $model->getPwmName() : __('Popup'));

        return $resultPage;
    }
}
