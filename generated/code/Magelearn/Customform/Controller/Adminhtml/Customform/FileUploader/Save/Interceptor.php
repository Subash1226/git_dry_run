<?php
namespace Magelearn\Customform\Controller\Adminhtml\Customform\FileUploader\Save;

/**
 * Interceptor class for @see \Magelearn\Customform\Controller\Adminhtml\Customform\FileUploader\Save
 */
class Interceptor extends \Magelearn\Customform\Controller\Adminhtml\Customform\FileUploader\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magelearn\Customform\Model\ImageUploader $imageUploader)
    {
        $this->___init();
        parent::__construct($context, $imageUploader);
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
