<?php
namespace MedizinhubCore\Patient\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use MedizinhubCore\Patient\Model\ResourceModel\TimeSlots\CollectionFactory as TimeSlotsCollection;
use MedizinhubCore\Patient\Model\ResourceModel\Hospitals\CollectionFactory as HospitalsCollection;
use MedizinhubCore\Patient\Model\ResourceModel\Practitioners\CollectionFactory as PractitionersCollection;

class Appointments extends Template
{
    protected $timeSlotsCollection;
    protected $hospitalsCollection;
    protected $practitionersCollection;

    public function __construct(
        Context $context,
        TimeSlotsCollection $timeSlotsCollection,
        HospitalsCollection $hospitalsCollection,
        PractitionersCollection $practitionersCollection,
        array $data = []
    ) {
        $this->timeSlotsCollection = $timeSlotsCollection;
        $this->hospitalsCollection = $hospitalsCollection;
        $this->practitionersCollection = $practitionersCollection;
        parent::__construct($context, $data);
    }

    // Fetch Time Slots
    public function getTimeSlots()
    {
        $timeSlots = [];
        $collection = $this->timeSlotsCollection->create()->addFieldToFilter('status', 1);

        foreach ($collection as $item) {
            $timeSlots[$item->getId()] = $item->getSlotTime();
        }

        return $timeSlots;
    }

    // Fetch Hospitals
    public function getHospitals()
    {
        $hospitals = [];
        $collection = $this->hospitalsCollection->create()->addFieldToFilter('status', 1);

        foreach ($collection as $item) {
            $hospitals[$item->getId()] = $item->getName();
        }

        return $hospitals;
    }

    // Fetch Practitioners with Special Price
    public function getPractitioners()
    {
        $practitioners = [];
        $collection = $this->practitionersCollection->create()->addFieldToFilter('status', 1);

        foreach ($collection as $item) {
            $practitioners[$item->getId()] = [
                'name' => $item->getName(),
                'special_price' => $item->getSpecialPrice()
            ];
        }

        return $practitioners;
    }
}
