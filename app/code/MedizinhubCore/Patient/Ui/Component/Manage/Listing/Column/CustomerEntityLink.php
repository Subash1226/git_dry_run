<?php

namespace MedizinhubCore\Patient\Ui\Component\Manage\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\ObjectManagerInterface;

class CustomerEntityLink extends Column
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ObjectManagerInterface $objectManager
     * @param array $components
     * @param array $data
     */
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

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['customer_id'])) {
                    $customerId = $item['customer_id'];
                    $customerName = $this->getCustomerNameById($customerId);
                    $customerUrl = $this->context->getUrl('customer/index/edit', ['id' => $customerId]);
                    $item['customer_name'] = '<a href="' . $customerUrl . '">' . $customerName . '</a>';
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get Customer Name by Customer ID
     *
     * @param int $customerId
     * @return string
     */
    private function getCustomerNameById($customerId)
    {
        $connection = $this->getConnection();
        $customerTable = $this->getTable('customer_entity');

        $select = $connection->select()
            ->from($customerTable, ['firstname', 'lastname'])
            ->where('entity_id = :entity_id');

        $bind = ['entity_id' => (int)$customerId];
        $result = $connection->fetchRow($select, $bind);

        if ($result) {
            return $result['firstname'] . ' ' . $result['lastname'];
        }

        return '';
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
