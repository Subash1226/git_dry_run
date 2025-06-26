<?php
namespace Magelearn\Customform\Controller\Adminhtml\Customform\InlineEdit;

/**
 * Interceptor class for @see \Magelearn\Customform\Controller\Adminhtml\Customform\InlineEdit
 */
class Interceptor extends \Magelearn\Customform\Controller\Adminhtml\Customform\InlineEdit implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $jsonFactory, \Magento\Backend\Model\Auth\Session $auth)
    {
        $this->___init();
        parent::__construct($context, $jsonFactory, $auth);
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
