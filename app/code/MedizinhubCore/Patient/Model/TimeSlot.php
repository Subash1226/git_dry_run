<?php
namespace MedizinhubCore\Patient\Model;

use Magento\Framework\Model\AbstractModel;
use MedizinhubCore\Patient\Model\ResourceModel\TimeSlot as TimeSlotResourceModel;

class TimeSlot extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TimeSlotResourceModel::class);
    }
}