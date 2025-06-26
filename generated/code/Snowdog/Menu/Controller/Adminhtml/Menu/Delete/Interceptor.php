<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\Delete;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\Delete
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Snowdog\Menu\Api\MenuRepositoryInterface $menuRepository, \Snowdog\Menu\Api\NodeRepositoryInterface $nodeRepository, \Magento\Framework\Api\FilterBuilderFactory $filterBuilderFactory, \Magento\Framework\Api\Search\FilterGroupBuilderFactory $filterGroupBuilderFactory, \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory, \Snowdog\Menu\Model\MenuFactory $menuFactory)
    {
        $this->___init();
        parent::__construct($context, $menuRepository, $nodeRepository, $filterBuilderFactory, $filterGroupBuilderFactory, $searchCriteriaBuilderFactory, $menuFactory);
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
