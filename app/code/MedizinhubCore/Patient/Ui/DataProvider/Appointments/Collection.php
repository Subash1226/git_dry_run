<?php

namespace MedizinhubCore\Patient\Ui\DataProvider\Appointments;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as BaseSearchResult;
use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends BaseSearchResult implements SearchResultInterface
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFilterToMap('appointment_id', 'main_table.appointment_id');

        $this->getSelect()->joinLeft(
            ['patient' => $this->getTable('patient')],
            'main_table.patient_id = patient.id',
            ['name']
        );

        $this->addExpressionFieldToSelect(
            'patient_name',
            '{{name}}',
            ['name' => 'patient.name']
        );

        $todayDate = date('Y-m-d');

        $this->getSelect()->order(new \Zend_Db_Expr("IF(main_table.date = '{$todayDate}', 0, 1), main_table.time_slot ASC, main_table.date ASC"));
    }
}
