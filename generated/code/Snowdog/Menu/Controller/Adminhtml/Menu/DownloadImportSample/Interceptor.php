<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\DownloadImportSample;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\DownloadImportSample
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\DownloadImportSample implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Psr\Log\LoggerInterface $logger, \Snowdog\Menu\Model\ImportExport\File\Download $fileDownload, \Snowdog\Menu\Model\ImportExport\Import\SampleData $sampleData)
    {
        $this->___init();
        parent::__construct($context, $logger, $fileDownload, $sampleData);
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
