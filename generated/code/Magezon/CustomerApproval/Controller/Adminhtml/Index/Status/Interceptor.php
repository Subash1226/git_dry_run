<?php
namespace Magezon\CustomerApproval\Controller\Adminhtml\Index\Status;

/**
 * Interceptor class for @see \Magezon\CustomerApproval\Controller\Adminhtml\Index\Status
 */
class Interceptor extends \Magezon\CustomerApproval\Controller\Adminhtml\Index\Status implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory, \Magezon\CustomerApproval\Model\Email $email, \Magezon\CustomerApproval\Helper\Data $dataHelper)
    {
        $this->___init();
        parent::__construct($context, $filter, $collectionFactory, $email, $dataHelper);
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
