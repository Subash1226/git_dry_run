<?php
namespace Snowdog\Menu\Model\GraphQl\Resolver\Menu\Field\Nodes;

/**
 * Interceptor class for @see \Snowdog\Menu\Model\GraphQl\Resolver\Menu\Field\Nodes
 */
class Interceptor extends \Snowdog\Menu\Model\GraphQl\Resolver\Menu\Field\Nodes implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\GraphQl\Query\ResolverInterface $nodeResolver)
    {
        $this->___init();
        parent::__construct($nodeResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(\Magento\Framework\GraphQl\Config\Element\Field $field, $context, \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info, ?array $value = null, ?array $args = null) : array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'resolve');
        return $pluginInfo ? $this->___callPlugins('resolve', func_get_args(), $pluginInfo) : parent::resolve($field, $context, $info, $value, $args);
    }
}
