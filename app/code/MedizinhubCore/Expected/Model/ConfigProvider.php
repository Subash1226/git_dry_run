<?php
namespace MedizinhubCore\Expected\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {
        return [
            'return_refund_policy' => $this->_layout->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate("MedizinhubCore_Expected::return_refund_policy.phtml")
                ->toHtml()
        ];
    }
}
