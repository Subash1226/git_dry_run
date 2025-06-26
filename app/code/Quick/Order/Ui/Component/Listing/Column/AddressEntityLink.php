<?php
namespace Quick\Order\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\ObjectManagerInterface;

class AddressEntityLink extends Column
{
    protected $objectManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->objectManager = $objectManager;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $connection = $this->getConnection();
                $selectId = $connection->select()
                    ->from($this->getTable('quick_order'))
                    ->where('id = ?', $item['id']);
                $addressId = $connection->fetchRow($selectId);

                if (isset($addressId['address_entity'])) {
                    $select = $connection->select()
                        ->from($this->getTable('customer_address_entity'))
                        ->where('entity_id = ?', $addressId['address_entity']);
                    $addressData = $connection->fetchRow($select);
                    if ($addressData) {
                        $item[$name] = $addressData['firstname'] . ' ' . $addressData['lastname'] . '<br>' .
                            $addressData['street'] . '<br>' . $addressData['city'] . ', ' . $addressData['region'] . ' ' . $addressData['postcode'] . '<br>' .
                            $addressData['country_id'] . '<br>' . $addressData['telephone'];
                    } else {
                        $item[$name] = __('Address not found');
                    }
                }
            }
        }

        return $dataSource;
    }

    private function getConnection()
    {
        return $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
    }

    private function getTable($tableName)
    {
        return $this->objectManager->get('Magento\Framework\App\ResourceConnection')->getTableName($tableName);
    }
}
