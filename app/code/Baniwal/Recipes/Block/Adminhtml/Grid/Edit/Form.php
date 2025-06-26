<?php
namespace Baniwal\Recipes\Block\Adminhtml\Grid\Edit;

/**
 * Adminhtml Add New Row Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Baniwal\Recipes\Model\Status $options,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('healthpackage_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => $model->getId() ? __('Edit Health Package') : __('Add Health Package'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'package_name',
            'text',
            [
                'name' => 'package_name',
                'label' => __('Package Name'),
                'id' => 'package_name',
                'title' => __('Package Name'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'category',
            'select',
            [
                'name' => 'category',
                'label' => __('Category'),
                'id' => 'category',
                'title' => __('Category'),
                'values' => [
                    ['label' => __('Full Body Checkup'), 'value' => 'fullbody'],
                    ['label' => __('Diabetes Package'), 'value' => 'diabetes'],
                    ['label' => __('Thyroid Test Package'), 'value' => 'thyroid'],
                    ['label' => __('Routine Test Package'), 'value' => 'routine'],
                    ['label' => __('Womens Health Package'), 'value' => 'womens'],
                    ['label' => __('Mens Health Package'), 'value' => 'mens'],
                    ['label' => __('Senior Citizen Package'), 'value' => 'senior'],
                    ['label' => __('Fever Test Package'), 'value' => 'fever'],
                    ['label' => __('Pregnancy'), 'value' => 'pregnancy'],
                    ['label' => __('Fitness'), 'value' => 'fitness'],
                    ['label' => __('General'), 'value' => 'general'],
                ],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'short_description',
            'text',
            [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'id' => 'short_description',
                'title' => __('Short Description'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'id' => 'price',
                'title' => __('Price'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'special_price',
            'text',
            [
                'name' => 'special_price',
                'label' => __('Special Price'),
                'id' => 'special_price',
                'title' => __('Special Price'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'style' => 'height:10em;',
                'required' => true,
            ]
        );


        $fieldset->addField(
            'importance',
            'text',
            [
                'name' => 'importance',
                'label' => __('Importance'),
                'id' => 'importance',
                'title' => __('Importance'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'included',
            'text',
            [
                'name' => 'included',
                'label' => __('No.of Tests Included '),
                'id' => 'included',
                'title' => __('Included'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'includedtest',
            'text',
            [
                'name' => 'includedtest',
                'label' => __('Included Packages'),
                'id' => 'includedtest',
                'title' => __('Included Packages'),
            ]
        );

        $fieldset->addField(
            'age',
            'text',
            [
                'name' => 'age',
                'label' => __('Age'),
                'id' => 'age',
                'title' => __('Age'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'gender',
            'select',
            [
                'name' => 'gender',
                'label' => __('Gender'),
                'id' => 'gender',
                'title' => __('Gender'),
                'values' => [
                    ['label' => __('Male'), 'value' => 'Male'],
                    ['label' => __('Female'), 'value' => 'Female'],
                    ['label' => __('Male and Female'), 'value' => 'Male,Female'],
                ],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'blood_group',
            'text',
            [
                'name' => 'blood_group',
                'label' => __('Sample Type'),
                'id' => 'blood_group',
                'title' => __('Sample Type'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'fasting_required',
            'select',
            [
                'name' => 'fasting_required',
                'label' => __('Fasting Required'),
                'id' => 'fasting_required',
                'title' => __('Fasting Required'),
                'values' => [
                    ['label' => __('No'), 'value' => 0],
                    ['label' => __('Yes'), 'value' => 1],
                ],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'id' => 'image',
                'title' => __('Image'),
                'required' => true,
                'note' => __('Allowed file types: jpg, jpeg, gif, png'),
                'preview_width' => '400',
                'preview_height' => '400',
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
