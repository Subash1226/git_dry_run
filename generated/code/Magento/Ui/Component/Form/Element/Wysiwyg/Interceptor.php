<?php
namespace Magento\Ui\Component\Form\Element\Wysiwyg;

/**
 * Interceptor class for @see \Magento\Ui\Component\Form\Element\Wysiwyg
 */
class Interceptor extends \Magento\Ui\Component\Form\Element\Wysiwyg implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Ui\Component\Wysiwyg\ConfigInterface $wysiwygConfig, array $components = [], array $data = [], array $config = [])
    {
        $this->___init();
        parent::__construct($context, $formFactory, $wysiwygConfig, $components, $data, $config);
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
