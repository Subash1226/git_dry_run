<?php

namespace MedizinhubCore\Patient\Block\Adminhtml\Appointments\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_coreRegistry;
    protected $_formFactory;
    protected $_wysiwygConfig;
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

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
            'legend' => __('Appointment Details'),
            'class' => 'fieldset-wide'
        ]);

        if ($model) {
            $fieldset->addField('appointment_id', 'hidden', ['name' => 'appointment_id']);
        }

        $fieldset->addField(
            'patient_id',
            'text',
            [
                'name' => 'patient_id',
                'label' => __('Patient ID'),
                'title' => __('Patient ID'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'practitioner_id',
            'select',
            [
                'name' => 'practitioner_id',
                'label' => __('Practitioner ID'),
                'title' => __('Practitioner ID'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => '1', 'label' => __('General Surgeon')],
                    ['value' => '2', 'label' => __('Diabetology Consult')],
                    ['value' => '3', 'label' => __('Pediatrician Consult')],
                    ['value' => '4', 'label' => __('Gynecologist Consult')],
                    ['value' => '5', 'label' => __('Orthopedics Consult')],
                    ['value' => '6', 'label' => __('Neurologist Consult')],
                ],
            ]
        );


        $fieldset->addField(
            'hospital_id',
            'select',
            [
                'name' => 'hospital_id',
                'label' => __('Hospital ID'),
                'title' => __('Hospital ID'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => '1', 'label' => __('Maha Clinic')],
                    ['value' => '2', 'label' => __('New Clinic')],
                    ['value' => '3', 'label' => __('Medizinhub Clinic')],
                ],
            ]
        );

        $fieldset->addField(
            'date',
            'date',
            [
                'name' => 'date',
                'label' => __('Appointment Date'),
                'title' => __('Appointment Date'),
                'class' => 'required-entry',
                'required' => true,
                'date_format' => 'yyyy-MM-dd',
                'time' => false,
                'min_date' => date('Y-m-d'),
            ]
        );


        $fieldset->addField(
            'time_slot',
            'select',
            [
                'name' => 'time_slot',
                'label' => __('Time Slot'),
                'title' => __('Time Slot'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->getTimeSlotOptions(),
            ]
        );


        $fieldset->addField(
            'patient_issue',
            'text',
            [
                'name' => 'patient_issue',
                'label' => __('Description'),
                'title' => __('Description'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addType(
            'multifile',
            'MedizinhubCore\Patient\Block\Adminhtml\Appointments\Form\Element\Html'
        );

        $reportDocIsRequired = true;

        if ($model && $model->getData('report_doc')) {
            $reportDocs = json_decode($model->getData('report_doc'), true);

            if (is_array($reportDocs) && !empty($reportDocs)) {
                $reportDocIsRequired = false;
            }
        }

        $fieldset->addField(
            'report_doc[]',
            'multifile',
            [
                'name' => 'report_doc[]',
                'label' => __('Report Document'),
                'title' => __('Report Document'),
                'class' => $reportDocIsRequired ? 'required-entry' : '',
                'required' => $reportDocIsRequired,
            ]
        );

        if (!$reportDocIsRequired) {
            $reportDocHtml = '';
            foreach ($reportDocs as $doc) {
                $doc = str_replace('\\', '/', $doc);
                $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $reportDocUrl = $mediaUrl . $doc;

                $reportDocHtml .= '<a href="' . $reportDocUrl . '" target="_blank">';
                $reportDocHtml .= '<img src="' . $reportDocUrl . '" width="150" alt="' . __('Report Document') . '" />';
                $reportDocHtml .= '</a><br>';
            }

            $fieldset->addField(
                'report_doc_preview',
                'note',
                [
                    'text' => $reportDocHtml,
                    'label' => __('Existing Report Document'),
                ]
            );
        }

        $fieldset->addField(
            'appointment_status',
            'select',
            [
                'name' => 'appointment_status',
                'label' => __('Appointment Status'),
                'title' => __('Appointment Status'),
                'class' => 'required-entry',
                'required' => true,
                'values' => [
                    ['value' => '1', 'label' => __('Requested')],
                    ['value' => '2', 'label' => __('In-Progress')],
                    ['value' => '3', 'label' => __('Completed')],
                ],
            ]
        );

        $form->setValues($model ? $model->getData() : []);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getTimeSlotOptions()
    {
        $options = [];
        $start_time = strtotime('8:00 AM');
        $end_time = strtotime('5:00 PM');
        $slot_counter = 1;

        while ($start_time < $end_time) {
            $time_slot_start = date('g:i A', $start_time);
            $time_slot_end = date('g:i A', strtotime('+1 hour', $start_time));
            $display_value = $time_slot_start . ' - ' . $time_slot_end;

            $options[] = [
                'value' => $slot_counter,
                'label' => $display_value
            ];

            $start_time = strtotime('+1 hour', $start_time);
            $slot_counter++;
        }

        return $options;
    }
}
