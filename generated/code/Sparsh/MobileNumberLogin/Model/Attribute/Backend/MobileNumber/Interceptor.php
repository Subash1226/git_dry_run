<?php
namespace Sparsh\MobileNumberLogin\Model\Attribute\Backend\MobileNumber;

/**
 * Interceptor class for @see \Sparsh\MobileNumberLogin\Model\Attribute\Backend\MobileNumber
 */
class Interceptor extends \Sparsh\MobileNumberLogin\Model\Attribute\Backend\MobileNumber implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory)
    {
        $this->___init();
        parent::__construct($customerCollectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'validate');
        return $pluginInfo ? $this->___callPlugins('validate', func_get_args(), $pluginInfo) : parent::validate($object);
    }
}
