<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Ajax\BuilderAssistance;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Ajax\BuilderAssistance
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Ajax\BuilderAssistance implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Goomento\PageBuilder\Api\ContentRegistryInterface $contentRegistry, \Magento\Framework\View\LayoutFactory $layoutFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Goomento\PageBuilder\Helper\Data $dataHelper, \Goomento\PageBuilder\Model\Config\Source\PageList $pageList, \Goomento\PageBuilder\Helper\BuildableContent $buildableContentHelper)
    {
        $this->___init();
        parent::__construct($context, $contentRegistry, $layoutFactory, $storeManager, $dataHelper, $pageList, $buildableContentHelper);
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
