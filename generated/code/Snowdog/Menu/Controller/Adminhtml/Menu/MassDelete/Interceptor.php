<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\MassDelete;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\MassDelete
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\MassDelete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Snowdog\Menu\Model\ResourceModel\Menu\CollectionFactory $collectionFactory, \Snowdog\Menu\Api\MenuRepositoryInterface $menuRepository)
    {
        $this->___init();
        parent::__construct($context, $filter, $collectionFactory, $menuRepository);
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
