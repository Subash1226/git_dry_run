<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\Export;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\Export
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\Export implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Psr\Log\LoggerInterface $logger, \Snowdog\Menu\Model\ImportExport\File\Download $fileDownload, \Snowdog\Menu\Model\ImportExport\Processor\Export $exportProcessor)
    {
        $this->___init();
        parent::__construct($context, $logger, $fileDownload, $exportProcessor);
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
