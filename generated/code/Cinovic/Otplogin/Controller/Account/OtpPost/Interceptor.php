<?php
namespace Cinovic\Otplogin\Controller\Account\OtpPost;

/**
 * Interceptor class for @see \Cinovic\Otplogin\Controller\Account\OtpPost
 */
class Interceptor extends \Cinovic\Otplogin\Controller\Account\OtpPost implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\CustomerFactory $customer, \Cinovic\Otplogin\Model\OtpFactory $otpFactory, \Magento\Customer\Model\Session $customersession, \Magento\Framework\Session\SessionManagerInterface $session, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Cinovic\Otplogin\Helper\Data $helper, \Magento\Customer\Model\ResourceModel\Customer\Collection $collection)
    {
        $this->___init();
        parent::__construct($context, $customer, $otpFactory, $customersession, $session, $scopeConfig, $resultJsonFactory, $helper, $collection);
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
