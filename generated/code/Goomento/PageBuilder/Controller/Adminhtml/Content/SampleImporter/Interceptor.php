<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Content\SampleImporter;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Content\SampleImporter
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Content\SampleImporter implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Goomento\PageBuilder\Model\LocalSampleCollection $localSampleCollection, \Goomento\PageBuilder\Api\SampleImporterInterface $sampleImporter, \Goomento\PageBuilder\Logger\Logger $logger)
    {
        $this->___init();
        parent::__construct($context, $localSampleCollection, $sampleImporter, $logger);
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
