<?php
namespace Snowdog\Menu\Controller\Adminhtml\Menu\Status;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Menu\Status
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Menu\Status implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Snowdog\Menu\Api\MenuRepositoryInterface $menuRepository, \Snowdog\Menu\Model\MenuFactory $menuFactory)
    {
        $this->___init();
        parent::__construct($context, $menuRepository, $menuFactory);
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
