<?php
namespace Lillik\PriceDecimal\Model\Currency;

/**
 * Interceptor class for @see \Lillik\PriceDecimal\Model\Currency
 */
class Interceptor extends \Lillik\PriceDecimal\Model\Currency implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\CacheInterface $appCache, \Lillik\PriceDecimal\Model\ConfigInterface $moduleConfig, $options = null, $locale = null)
    {
        $this->___init();
        parent::__construct($appCache, $moduleConfig, $options, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function toCurrency($value = null, array $options = []) : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'toCurrency');
        return $pluginInfo ? $this->___callPlugins('toCurrency', func_get_args(), $pluginInfo) : parent::toCurrency($value, $options);
    }
}
