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

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;
    
    
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_PopupWindowMessage::save');
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        

        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            try {
                $model = $this->_objectManager->create(\FME\PopupWindowMessage\Model\Popup::class);
                $id = $this->getRequest()->getParam('pwm_id');

                if ($id) {
                    $model->load($id);
                }

                if (!empty($data['cmspage_ids'])) {
                    $data['cmspage_ids'] = implode(',', $data['cmspage_ids']);
                } else {
                    $data['cmspage_ids'] = '';
                }

                if (!empty($data["photogallery_categories"])) {
                    $arr = $data["photogallery_categories"];
                    $str = implode(",", $arr);
                    $data["popup_categories"] = $str;
                }
               

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }
                
                if ($data['pwm_id'] == '') {
                    unset($data['pwm_id']);
                }
                $model->loadPost($data);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());
                $this->dataPersistor->set('percentage_pricing_rule', $data);
                $model->save();

                $this->messageManager->addSuccess(__('Popup saved successfully.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData(false);
                $this->dataPersistor->clear('percentage_pricing_rule');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the popup.').$e->getMessage());
            }
            
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('pwm_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
