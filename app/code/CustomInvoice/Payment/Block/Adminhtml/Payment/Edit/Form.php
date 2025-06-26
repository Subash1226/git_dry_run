<?php

namespace CustomInvoice\Payment\Block\Adminhtml\Payment\Edit;

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
        $this->_coreRegistry = $registry;
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
        $form->setHtmlIdPrefix('custompayment_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Payment Details'),
            'class' => 'fieldset-wide'
        ]);

        if ($model) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $isDisabled = ($model && $model->getId()) ? true : false;

        $fieldset->addField(
            'customer_name',
            'text',
            [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'style' => 'width:65%;',
                'class' => 'required-entry customer_name validate-alpha-only',
                'required' => true,
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'customer_phone',
            'text',
            [
                'name' => 'customer_phone',
                'label' => __('Customer Phone No.'),
                'title' => __('Customer Phone No.'),
                'style' => 'width:65%;',
                'class' => 'required-entry customer_phone validate-digits validate-length minimum-length-10 maximum-length-10',
                'required' => true,
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'customer_email',
            'text',
            [
                'name' => 'customer_email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'style' => 'width:65%;',
                'class' => 'customer_email validate-email',
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'customer_address',
            'text',
            [
                'name' => 'customer_address',
                'label' => __('Customer Address'),
                'title' => __('Customer Address'),
                'style' => 'width:65%;',
                'class' => 'customer_address',
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'payment_description',
            'textarea',
            [
                'name' => 'payment_description',
                'label' => __('Payment Description'),
                'title' => __('Payment Description'),
                'style' => 'width:65%;',
                'class' => 'required-entry payment_description',
                'required' => true,
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'payment_type',
            'select',
            [
                'name' => 'payment_type',
                'label' => __('Payment Type'),
                'title' => __('Payment Type'),
                'style' => 'width:65%;',
                'class' => 'required-entry payment_type',
                'required' => true,
                'disabled' => $isDisabled,
                'values' => [
                    ['value' => 'upi', 'label' => __('UPI')],
                    ['value' => 'credit_card', 'label' => __('Credit Card')],
                    ['value' => 'debit_card', 'label' => __('Debit Card')],
                    ['value' => 'net_banking', 'label' => __('Net Banking')]
                ]
            ]
        );

        $fieldset->addField(
            'total_amount',
            'text',
            [
                'name' => 'total_amount',
                'label' => __('Total Amount'),
                'title' => __('Total Amount'),
                'style' => 'width:65%;',
                'class' => 'required-entry total_amount validate-number validate-greater-than-zero',
                'required' => true,
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'cash_amount',
            'text',
            [
                'name' => 'cash_amount',
                'label' => __('Cash Amount'),
                'title' => __('Cash Amount'),
                'style' => 'width:65%;',
                'class' => 'cash_amount validate-number validate-less-than-or-equal-total-amount',
                'disabled' => $isDisabled
            ]
        );

        $fieldset->addField(
            'online_amount',
            'text',
            [
                'name' => 'online_amount',
                'label' => __('Overall Amount (Online Payment)'),
                'title' => __('Online Payment Amount'),
                'style' => 'width:65%;',
                'class' => 'required-entry online_amount validate-number validate-greater-than-zero',
                'required' => true,
                'disabled' => $isDisabled
            ]
        );

        if (!$model || !$model->getId()) {
            $fieldset->addField(
                'make_payment',
                'label',
                [
                    'name' => 'make_payment',
                    'value' => __('Make Payment'),
                    'after_element_html' => 
                        '<button type="button" class="custom-button-class" ' .
                        'style="background-color: #ba4000; border-color: #b84002; color: #ffffff; padding: 10px; text-decoration: none; margin:auto 66%; margin-top: 10px; width:150px;">' .
                        __('Make Payment') . 
                        '</button>'
                ]
            );
        }

        $form->setValues($model ? $model->getData() : []);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}