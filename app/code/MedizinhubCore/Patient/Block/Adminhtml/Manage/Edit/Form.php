<?php

namespace MedizinhubCore\Patient\Block\Adminhtml\Manage\Edit;

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
        $form->setHtmlIdPrefix('patient_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Patient Details'),
            'class' => 'fieldset-wide'
        ]);

        if ($model) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Patient Full Name'),
                'title' => __('Patient Full Name'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'date_of_birth',
            'date',
            [
                'name' => 'date_of_birth',
                'label' => __('Date of Birth'),
                'title' => __('Date of Birth'),
                'date_format' => 'yyyy-MM-dd',
                'class' => 'required-entry',
                'required' => true,
                'date_format' => 'yyyy-MM-dd',
                'time' => false,
                'max_date' => date('Y-m-d'),
                'id' => 'date_of_birth',
            ]
        );


        $fieldset->addField(
            'age',
            'text',
            [
                'name' => 'age',
                'label' => __('Patient Age'),
                'title' => __('Patient Age'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Patient Email'),
                'title' => __('Patient Email'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'blood_group',
            'select',
            [
                'name' => 'blood_group',
                'label' => __('Blood Group'),
                'title' => __('Blood Group'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => 'A+', 'label' => __('A+')],
                    ['value' => 'A-', 'label' => __('A-')],
                    ['value' => 'B+', 'label' => __('B+')],
                    ['value' => 'B-', 'label' => __('B-')],
                    ['value' => 'O+', 'label' => __('O+')],
                    ['value' => 'O-', 'label' => __('O-')],
                    ['value' => 'AB+', 'label' => __('AB+')],
                    ['value' => 'AB-', 'label' => __('AB-')],
                ],
            ]
        );


        $fieldset->addField(
            'gender',
            'select',
            [
                'name' => 'gender',
                'label' => __('Gender'),
                'title' => __('Gender'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => 'male', 'label' => __('Male')],
                    ['value' => 'female', 'label' => __('Female')],
                    ['value' => 'transgender', 'label' => __('Transgender')],
                ],
            ]
        );


        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Mobile Number'),
                'title' => __('Mobile Number'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'whatsapp',
            'text',
            [
                'name' => 'whatsapp',
                'label' => __('WhatsApp Number'),
                'title' => __('WhatsApp Number'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'house_no',
            'text',
            [
                'name' => 'house_no',
                'label' => __('House No'),
                'title' => __('House No'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'street',
            'text',
            [
                'name' => 'street',
                'label' => __('Street'),
                'title' => __('Street'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'area',
            'text',
            [
                'name' => 'area',
                'label' => __('Area'),
                'title' => __('Area'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'title' => __('City'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'postcode',
            'text',
            [
                'name' => 'postcode',
                'label' => __('Pincode'),
                'title' => __('Pincode'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'country_id',
            'text',
            [
                'name' => 'country_id',
                'label' => __('Country ID'),
                'title' => __('Country ID'),
                'class' => 'required-entry',
                'required' => true,
                'value' => 'IN',
                // 'readonly' => true
            ]
        );

        $fieldset->addField(
            'region_id',
            'text',
            [
                'name' => 'region_id',
                'label' => __('Region ID'),
                'title' => __('Region ID'),
                'class' => 'required-entry',
                'required' => true,
                'value' => '563',
                // 'readonly' => true
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => '1', 'label' => __('Active')],
                    ['value' => '0', 'label' => __('In-Active')],
                ],
            ]
        );

        $fieldset->addField(
            'customer_id',
            'text',
            [
                'name' => 'customer_id',
                'label' => __('Customer Id'),
                'title' => __('Customer Id'),
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
