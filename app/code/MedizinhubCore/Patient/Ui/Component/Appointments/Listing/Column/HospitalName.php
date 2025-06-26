<?php
namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class HospitalName extends Column
{
    protected $resource;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resource = ObjectManager::getInstance()->get(ResourceConnection::class);
    }

    public function prepareDataSource(array $dataSource)
    {
        $connection = $this->resource->getConnection();
        $hospitalsQuery = "SELECT id, name FROM hospitals WHERE status = 1";
        $hospitals = $connection->fetchPairs($hospitalsQuery);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['hospital_id']) && isset($hospitals[$item['hospital_id']])) {
                    $item['hospital_id'] = $hospitals[$item['hospital_id']];
                }
            }
        }

        return $dataSource;
    }
}
