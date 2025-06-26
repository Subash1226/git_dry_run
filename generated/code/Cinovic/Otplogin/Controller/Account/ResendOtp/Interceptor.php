<?php
namespace Cinovic\Otplogin\Controller\Account\ResendOtp;

/**
 * Interceptor class for @see \Cinovic\Otplogin\Controller\Account\ResendOtp
 */
class Interceptor extends \Cinovic\Otplogin\Controller\Account\ResendOtp implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Session\SessionManagerInterface $session, \Cinovic\Otplogin\Helper\Data $helper, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->___init();
        parent::__construct($context, $session, $helper, $resultJsonFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
