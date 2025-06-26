<?php
namespace Magento\Shipping\Model\Rate\Result;

/**
 * Interceptor class for @see \Magento\Shipping\Model\Rate\Result
 */
class Interceptor extends \Magento\Shipping\Model\Rate\Result implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->___init();
        parent::__construct($storeManager);
    }

    /**
     * {@inheritdoc}
     */
    public function append($result)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'append');
        return $pluginInfo ? $this->___callPlugins('append', func_get_args(), $pluginInfo) : parent::append($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRates()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getAllRates');
        return $pluginInfo ? $this->___callPlugins('getAllRates', func_get_args(), $pluginInfo) : parent::getAllRates();
    }
}
