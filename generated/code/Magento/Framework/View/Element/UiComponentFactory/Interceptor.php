<?php
namespace Magento\Framework\View\Element\UiComponentFactory;

/**
 * Interceptor class for @see \Magento\Framework\View\Element\UiComponentFactory
 */
class Interceptor extends \Magento\Framework\View\Element\UiComponentFactory implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\View\Element\UiComponent\Config\ManagerInterface $componentManager, \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter, \Magento\Framework\View\Element\UiComponent\ContextFactory $contextFactory, array $data = [], array $componentChildFactories = [], ?\Magento\Framework\Config\DataInterface $definitionData = null, ?\Magento\Framework\Config\DataInterfaceFactory $configFactory = null, ?\Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer $sanitizer = null)
    {
        $this->___init();
        parent::__construct($objectManager, $componentManager, $argumentInterpreter, $contextFactory, $data, $componentChildFactories, $definitionData, $configFactory, $sanitizer);
    }

    /**
     * {@inheritdoc}
     */
    public function create($identifier, $name = null, array $arguments = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'create');
        return $pluginInfo ? $this->___callPlugins('create', func_get_args(), $pluginInfo) : parent::create($identifier, $name, $arguments);
    }
}
