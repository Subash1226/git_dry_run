<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Content\Importer;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Content\Importer
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Content\Importer implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Goomento\PageBuilder\Api\ContentImportProcessorInterface $importProcessor, \Goomento\PageBuilder\Logger\Logger $logger)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $importProcessor, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }
}
