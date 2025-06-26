<?php
namespace Sp\Orderattachment\Controller\Attachment\Delete;

/**
 * Interceptor class for @see \Sp\Orderattachment\Controller\Attachment\Delete
 */
class Interceptor extends \Sp\Orderattachment\Controller\Attachment\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Sp\Orderattachment\Helper\Attachment $attachmentHelper)
    {
        $this->___init();
        parent::__construct($context, $resultRawFactory, $attachmentHelper);
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
