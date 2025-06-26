<?php

namespace MedizinhubCore\Patient\Block\Adminhtml\Appointments;

/**
 * Class Edit
 * @package MedizinhubCore\Patient\Block\Adminhtml\Appointments
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'appointment_id';
        $this->_blockGroup = 'MedizinhubCore_Patient';
        $this->_controller = 'adminhtml_appointments';
        parent::_construct();
        if ($this->_isAllowedAction('MedizinhubCore_Patient::edit')) {
            $this->buttonList->update('save', 'label', __('Save'));
        } else {
            $this->buttonList->remove('save');
        }
        $this->buttonList->remove('reset');
    }

    public function getHeaderText()
    {
        return __('Edit Appointments');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
