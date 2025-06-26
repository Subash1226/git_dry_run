<?php
namespace Mhb\RestrictCod\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

class States implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @param CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        CollectionFactory $regionCollectionFactory
    ) {
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Get states as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $regionCollection = $this->regionCollectionFactory->create()
            ->addCountryFilter('IN') // You can modify this to include other countries if needed
            ->setOrder('name', 'ASC');

        foreach ($regionCollection as $region) {
            $options[] = [
                'value' => $region->getCode(),
                'label' => $region->getName()
            ];
        }

        return $options;
    }
}