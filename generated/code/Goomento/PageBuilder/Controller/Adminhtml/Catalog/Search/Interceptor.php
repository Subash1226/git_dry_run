<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Catalog\Search;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Catalog\Search
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Catalog\Search implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Goomento\PageBuilder\Model\BetterCaching $betterCaching, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory)
    {
        $this->___init();
        parent::__construct($context, $betterCaching, $productCollectionFactory);
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
