<?php

namespace Cinovic\Otplogin\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'developer', 'label' => __('Developer Mode')],
            ['value' => 'production', 'label' => __('Production Mode')],
        ];
    }
}
