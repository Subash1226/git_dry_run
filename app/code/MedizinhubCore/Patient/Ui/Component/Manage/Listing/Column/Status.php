<?php

namespace MedizinhubCore\Patient\Ui\Component\Manage\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['status'])) {
                    $item['status'] = $item['status'] == 1 ? __('Active') : __('In-Active');
                }
            }
        }

        return $dataSource;
    }
}
