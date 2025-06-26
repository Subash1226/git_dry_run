<?php
namespace Goomento\PageBuilder\Controller\Router;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Router
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Router implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\ActionFactory $actionFactory, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\UrlInterface $url, \Goomento\PageBuilder\Model\ContentRegistry $contentRegistry, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\ResponseInterface $response)
    {
        $this->___init();
        parent::__construct($actionFactory, $eventManager, $url, $contentRegistry, $storeManager, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'match');
        return $pluginInfo ? $this->___callPlugins('match', func_get_args(), $pluginInfo) : parent::match($request);
    }
}
