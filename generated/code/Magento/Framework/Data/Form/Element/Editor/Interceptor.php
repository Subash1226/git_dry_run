<?php
namespace Magento\Framework\Data\Form\Element\Editor;

/**
 * Interceptor class for @see \Magento\Framework\Data\Form\Element\Editor
 */
class Interceptor extends \Magento\Framework\Data\Form\Element\Editor implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Data\Form\Element\Factory $factoryElement, \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection, \Magento\Framework\Escaper $escaper, $data = [], ?\Magento\Framework\Serialize\Serializer\Json $serializer = null, ?\Magento\Framework\Math\Random $random = null, ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null)
    {
        $this->___init();
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $serializer, $random, $secureRenderer);
    }

    /**
     * {@inheritdoc}
     */
    public function getElementHtml()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getElementHtml');
        return $pluginInfo ? $this->___callPlugins('getElementHtml', func_get_args(), $pluginInfo) : parent::getElementHtml();
    }
}
