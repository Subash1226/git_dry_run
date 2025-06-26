<?php
namespace Snowdog\Menu\Controller\Adminhtml\Node\UploadImage;

/**
 * Interceptor class for @see \Snowdog\Menu\Controller\Adminhtml\Node\UploadImage
 */
class Interceptor extends \Snowdog\Menu\Controller\Adminhtml\Node\UploadImage implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory, \Psr\Log\LoggerInterface $logger, \Snowdog\Menu\Model\Menu\Node\Image\File $imageFile, \Snowdog\Menu\Model\Menu\Node\Image\Node $imageNode)
    {
        $this->___init();
        parent::__construct($context, $jsonResultFactory, $logger, $imageFile, $imageNode);
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
