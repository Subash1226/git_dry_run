<?php
namespace Magento\Catalog\Ui\Component\Category\Form\Element\Wysiwyg;

/**
 * Interceptor class for @see \Magento\Catalog\Ui\Component\Category\Form\Element\Wysiwyg
 */
class Interceptor extends \Magento\Catalog\Ui\Component\Category\Form\Element\Wysiwyg implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Ui\Component\Wysiwyg\ConfigInterface $wysiwygConfig, \Magento\Framework\View\LayoutInterface $layout, \Magento\Backend\Helper\Data $backendHelper, \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $attrRepository, array $components = [], array $data = [], array $config = [])
    {
        $this->___init();
        parent::__construct($context, $formFactory, $wysiwygConfig, $layout, $backendHelper, $attrRepository, $components, $data, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'prepare');
        return $pluginInfo ? $this->___callPlugins('prepare', func_get_args(), $pluginInfo) : parent::prepare();
    }
}
