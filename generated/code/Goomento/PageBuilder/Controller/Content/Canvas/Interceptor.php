<?php
namespace Goomento\PageBuilder\Controller\Content\Canvas;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Content\Canvas
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Content\Canvas implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Goomento\PageBuilder\Logger\Logger $logger, \Goomento\Core\Model\Registry $registry, \Goomento\PageBuilder\Helper\Data $dataHelper)
    {
        $this->___init();
        parent::__construct($context, $logger, $registry, $dataHelper);
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
