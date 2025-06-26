<?php

namespace MedizinhubCore\Sample\Block\Adminhtml\Manage\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
        ]);
        $form->setHtmlIdPrefix('labtest_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Entry Details'),
            'class' => 'fieldset-wide'
        ]);

        if ($model) {
            $fieldset->addField('test_id', 'hidden', ['name' => 'test_id']);
        }

        $fieldset->addField(
            'test_name',
            'text',
            [
                'name' => 'test_name',
                'label' => __('Test Name'),
                'title' => __('Test Name'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'appointment_time',
            'text',
            [
                'name' => 'appointment_time',
                'label' => __('Appointment Time'),
                'title' => __('Appointment Time'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );


        $fieldset->addField(
            'appointment_date',
            'text',
            [
                'name' => 'appointment_date',
                'label' => __('Appointment Date'),
                'title' => __('Appointment Date'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'customer_id',
            'text',
            [
                'name' => 'customer_id',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'patient',
            'text',
            [
                'name' => 'patient',
                'label' => __('Patient'),
                'title' => __('Patient'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'address_id',
            'text',
            [
                'name' => 'address_id',
                'label' => __('Address ID'),
                'title' => __('Address ID'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'test_type',
            'text',
            [
                'name' => 'test_type',
                'label' => __('Test Type'),
                'title' => __('Test Type'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'mobile_number',
            'text',
            [
                'name' => 'mobile_number',
                'label' => __('Mobile Number'),
                'title' => __('Mobile Number'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'total_price',
            'text',
            [
                'name' => 'total_price',
                'label' => __('Total Price'),
                'title' => __('Total Price'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'payment_type',
            'text',
            [
                'name' => 'payment_type',
                'label' => __('Payment Type'),
                'title' => __('Payment Type'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'transaction_id',
            'text',
            [
                'name' => 'transaction_id',
                'label' => __('Transaction ID'),
                'title' => __('Transaction ID'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $form->setValues($model ? $model->getData() : []);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
