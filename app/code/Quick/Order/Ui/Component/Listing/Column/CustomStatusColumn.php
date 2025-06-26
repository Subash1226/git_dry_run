<?php
namespace Quick\Order\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class CustomStatusColumn extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Get the status as a string
                $status = $item[$this->getData('name')];
                $label = '';
                switch ($status) {
                    case '0':
                        $label = __('Order Cancelled');
                        break;
                    case '1':
                        $label = __('Order Placed');
                        break;
                    case '2':
                        $label = __('Order Under in Review');
                        break;
                    case '3':
                        $label = __('Order Accepted');
                        $item['show_create_order_link'] = true;
                        break;
                    case '4':
                        $label = __('Order Rejected');
                        break;
                    default:
                        $label = __('Unknown');
                }
                $item[$this->getData('name')] = $label;
            }
        }
        return $dataSource;
    }
}
