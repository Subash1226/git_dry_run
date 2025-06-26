<?php
namespace Magento\Framework\Currency;

/**
 * Interceptor class for @see \Magento\Framework\Currency
 */
class Interceptor extends \Magento\Framework\Currency implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\CacheInterface $appCache, $options = null, $locale = null)
    {
        $this->___init();
        parent::__construct($appCache, $options, $locale);
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
