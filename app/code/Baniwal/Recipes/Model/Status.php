<?php

namespace Baniwal\Recipes\Model;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Get status options as key-value pairs.
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            '1' => __('Enabled'),
            '0' => __('Disabled')
        ];
    }

    /**
     * Get status options with an empty option for dropdowns.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = $this->getOptions();
        array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);
        return $options;
    }

    /**
     * Get status options formatted for dropdown elements.
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->getOptionArray() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }

    /**
     * Get options in the format required by Magento forms.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
