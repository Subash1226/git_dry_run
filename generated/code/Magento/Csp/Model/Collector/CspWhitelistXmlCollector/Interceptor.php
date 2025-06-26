<?php
namespace Magento\Csp\Model\Collector\CspWhitelistXmlCollector;

/**
 * Interceptor class for @see \Magento\Csp\Model\Collector\CspWhitelistXmlCollector
 */
class Interceptor extends \Magento\Csp\Model\Collector\CspWhitelistXmlCollector implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Config\DataInterface $configReader)
    {
        $this->___init();
        parent::__construct($configReader);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(array $defaultPolicies = []) : array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'collect');
        return $pluginInfo ? $this->___callPlugins('collect', func_get_args(), $pluginInfo) : parent::collect($defaultPolicies);
    }
}
