<?php
namespace Magento\PageBuilder\Component\Form\Element\Wysiwyg;

/**
 * Interceptor class for @see \Magento\PageBuilder\Component\Form\Element\Wysiwyg
 */
class Interceptor extends \Magento\PageBuilder\Component\Form\Element\Wysiwyg implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Ui\Component\Wysiwyg\ConfigInterface $wysiwygConfig, \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $attrRepository, \Magento\PageBuilder\Model\State $pageBuilderState, \Magento\PageBuilder\Model\Stage\Config $stageConfig, array $components = [], array $data = [], array $config = [], ?\Magento\PageBuilder\Model\Config $pageBuilderConfig = null, bool $overrideSnapshot = false, ?\Magento\Framework\View\Asset\Repository $assetRepo = null)
    {
        $this->___init();
        parent::__construct($context, $formFactory, $wysiwygConfig, $attrRepository, $pageBuilderState, $stageConfig, $components, $data, $config, $pageBuilderConfig, $overrideSnapshot, $assetRepo);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'prepare');
        return $pluginInfo ? $this->___callPlugins('prepare', func_get_args(), $pluginInfo) : parent::prepare();
    }
}
