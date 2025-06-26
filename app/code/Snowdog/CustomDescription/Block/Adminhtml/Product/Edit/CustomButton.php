<?php
namespace Snowdog\CustomDescription\Block\Adminhtml\Product\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CustomButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Custom Button'),
            'class' => 'action-secondary',
            'on_click' => 'alert("Custom button clicked!")',
            'sort_order' => 50,
        ];
    }
}
