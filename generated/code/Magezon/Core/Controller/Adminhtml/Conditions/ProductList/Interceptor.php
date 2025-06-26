<?php
namespace Magezon\Core\Controller\Adminhtml\Conditions\ProductList;

/**
 * Interceptor class for @see \Magezon\Core\Controller\Adminhtml\Conditions\ProductList
 */
class Interceptor extends \Magezon\Core\Controller\Adminhtml\Conditions\ProductList implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Framework\View\LayoutFactory $layoutFactory, \Magento\Framework\Registry $registry, \Magezon\Core\Block\Adminhtml\Conditions\Product $gridProduct)
    {
        $this->___init();
        parent::__construct($context, $resultRawFactory, $layoutFactory, $registry, $gridProduct);
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
