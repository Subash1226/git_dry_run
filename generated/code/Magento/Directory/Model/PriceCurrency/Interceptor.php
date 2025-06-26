<?php
namespace Magento\Directory\Model\PriceCurrency;

/**
 * Interceptor class for @see \Magento\Directory\Model\PriceCurrency
 */
class Interceptor extends \Magento\Directory\Model\PriceCurrency implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Directory\Model\CurrencyFactory $currencyFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($storeManager, $currencyFactory, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = 2)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'convertAndRound');
        return $pluginInfo ? $this->___callPlugins('convertAndRound', func_get_args(), $pluginInfo) : parent::convertAndRound($amount, $scope, $currency, $precision);
    }

    /**
     * {@inheritdoc}
     */
    public function format($amount, $includeContainer = true, $precision = 2, $scope = null, $currency = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'format');
        return $pluginInfo ? $this->___callPlugins('format', func_get_args(), $pluginInfo) : parent::format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndFormat($amount, $includeContainer = true, $precision = 2, $scope = null, $currency = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'convertAndFormat');
        return $pluginInfo ? $this->___callPlugins('convertAndFormat', func_get_args(), $pluginInfo) : parent::convertAndFormat($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function round($price)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'round');
        return $pluginInfo ? $this->___callPlugins('round', func_get_args(), $pluginInfo) : parent::round($price);
    }
}
