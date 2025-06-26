<?php
namespace MedizinhubCore\Patient\Ui\Component\Appointments\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class TimeSlot extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var array|null
     */
    protected $timeSlots = null;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->resource = ObjectManager::getInstance()->get(ResourceConnection::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Get Time Slots from Database
     *
     * @return array
     */
    protected function getTimeSlots()
    {
        if ($this->timeSlots === null) {
            $connection = $this->resource->getConnection();
            $query = "SELECT id, slot_time FROM time_slots WHERE status = 1";
            $this->timeSlots = $connection->fetchPairs($query);
        }
        return $this->timeSlots;
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
            $timeSlots = $this->getTimeSlots();

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['time_slot']) && isset($timeSlots[$item['time_slot']])) {
                    $item['time_slot'] = $this->escaper->escapeHtml($timeSlots[$item['time_slot']]);
                } else {
                    $item['time_slot'] = $this->escaper->escapeHtml(__('Invalid Slot'));
                }
            }
        }

        return $dataSource;
    }
}
