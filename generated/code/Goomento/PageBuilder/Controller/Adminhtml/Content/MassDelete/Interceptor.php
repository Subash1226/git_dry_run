<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Content\MassDelete;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Content\MassDelete
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Content\MassDelete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Goomento\PageBuilder\Model\ResourceModel\Content\CollectionFactory $collectionFactory, \Goomento\PageBuilder\Api\BuildableContentManagementInterface $contentManagement, \Goomento\PageBuilder\Api\ContentRegistryInterface $contentRegistry, \Goomento\PageBuilder\Logger\Logger $logger)
    {
        $this->___init();
        parent::__construct($context, $filter, $collectionFactory, $contentManagement, $contentRegistry, $logger);
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
