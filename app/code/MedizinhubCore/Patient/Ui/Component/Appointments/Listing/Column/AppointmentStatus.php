<?php

namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class AppointmentStatus extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        $hospitalMap = [
            1 => __('Requested'),
            2 => __('In-Progress'),
            3 => __('Completed'),
        ];

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['appointment_status']) && isset($hospitalMap[$item['appointment_status']])) {
                    $item['appointment_status'] = $hospitalMap[$item['appointment_status']];
                }
            }
        }

        return $dataSource;
    }
}
