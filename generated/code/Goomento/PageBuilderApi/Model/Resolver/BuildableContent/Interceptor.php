<?php
namespace Goomento\PageBuilderApi\Model\Resolver\BuildableContent;

/**
 * Interceptor class for @see \Goomento\PageBuilderApi\Model\Resolver\BuildableContent
 */
class Interceptor extends \Goomento\PageBuilderApi\Model\Resolver\BuildableContent implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\GraphQl\Query\Resolver\ValueFactory $valueFactory, \Goomento\PageBuilderApi\Api\BuildableContentPublicRepositoryInterface $buildableContentPublicRepository)
    {
        $this->___init();
        parent::__construct($valueFactory, $buildableContentPublicRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(\Magento\Framework\GraphQl\Config\Element\Field $field, $context, \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'resolve');
        return $pluginInfo ? $this->___callPlugins('resolve', func_get_args(), $pluginInfo) : parent::resolve($field, $context, $info, $value, $args);
    }
}
