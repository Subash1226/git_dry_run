<?php
namespace Goomento\PageBuilder\Controller\Adminhtml\Content\Delete;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Controller\Adminhtml\Content\Delete
 */
class Interceptor extends \Goomento\PageBuilder\Controller\Adminhtml\Content\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Goomento\PageBuilder\Controller\Adminhtml\Content\ContentDataProcessor $dataProcessor, \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor, \Goomento\PageBuilder\Helper\AdminUser $userHelper, \Magento\Framework\View\Result\PageFactory $pageFactory, \Goomento\Core\Model\Registry $registry, ?\Goomento\PageBuilder\Api\ContentRegistryInterface $contentRegistry = null, ?\Goomento\PageBuilder\Api\BuildableContentManagementInterface $contentManagement = null, ?\Goomento\PageBuilder\Logger\Logger $logger = null)
    {
        $this->___init();
        parent::__construct($context, $dataProcessor, $dataPersistor, $userHelper, $pageFactory, $registry, $contentRegistry, $contentManagement, $logger);
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
