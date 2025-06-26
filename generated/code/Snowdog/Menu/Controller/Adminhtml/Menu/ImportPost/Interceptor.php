<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\ImportPost;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\ImportPost
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\ImportPost implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Psr\Log\LoggerInterface $logger, \Snowdog\Menu\Model\ImportExport\File\Upload $fileUpload, \Snowdog\Menu\Model\ImportExport\Processor\Import $importProcessor)
    {
        $this->___init();
        parent::__construct($context, $logger, $fileUpload, $importProcessor);
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
