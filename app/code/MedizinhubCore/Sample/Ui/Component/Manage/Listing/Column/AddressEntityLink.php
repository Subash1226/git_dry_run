<?php

namespace MedizinhubCore\Sample\Ui\Component\Manage\Listing\Column;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AddressEntityLink extends Column
{
    protected $resourceConnection;

    /**
     * Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResourceConnection $resourceConnection
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resourceConnection,
        array $components = [],
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Retrieve address by ID
     *
     * @param int $addressId
     * @return array
     */
    public function getAddressById($addressId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('patient'); // Custom table name

        $select = $connection->select()
            ->from($tableName)
            ->where('id = ?', $addressId);

        return $connection->fetchRow($select);
    }

    /**
     * Prepare data source for grid
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $addressId = isset($item['address_id']) ? $item['address_id'] : null;
                if ($addressId) {
                    $address = $this->getAddressById($addressId);
                    if ($address) {
                        $item['address_details'] = sprintf(
                            'Address: %s, %s, %s, %s',
                            $address['house_no'],
                            $address['street'],
                            $address['city'],
                            $address['postcode']
                        );
                    } else {
                        $item['address_details'] = 'Address not found';
                    }
                }
            }
        }
        return $dataSource;
    }
}
