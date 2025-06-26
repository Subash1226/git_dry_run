<?php
namespace Magento\Captcha\Model\DefaultModel;

/**
 * Interceptor class for @see \Magento\Captcha\Model\DefaultModel
 */
class Interceptor extends \Magento\Captcha\Model\DefaultModel implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Session\SessionManagerInterface $session, \Magento\Captcha\Helper\Data $captchaData, \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory, $formId, ?\Magento\Framework\Math\Random $randomMath = null, ?\Magento\Authorization\Model\UserContextInterface $userContext = null)
    {
        $this->___init();
        parent::__construct($session, $captchaData, $resLogFactory, $formId, $randomMath, $userContext);
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'generate');
        return $pluginInfo ? $this->___callPlugins('generate', func_get_args(), $pluginInfo) : parent::generate();
    }
}
