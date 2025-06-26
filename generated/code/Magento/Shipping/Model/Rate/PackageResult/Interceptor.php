<?php
namespace Magento\Shipping\Model\Rate\PackageResult;

/**
 * Interceptor class for @see \Magento\Shipping\Model\Rate\PackageResult
 */
class Interceptor extends \Magento\Shipping\Model\Rate\PackageResult implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $errorFactory)
    {
        $this->___init();
        parent::__construct($storeManager, $errorFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllRates()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getAllRates');
        return $pluginInfo ? $this->___callPlugins('getAllRates', func_get_args(), $pluginInfo) : parent::getAllRates();
    }

    /**
     * {@inheritdoc}
     */
    public function append($result)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'append');
        return $pluginInfo ? $this->___callPlugins('append', func_get_args(), $pluginInfo) : parent::append($result);
    }
}
