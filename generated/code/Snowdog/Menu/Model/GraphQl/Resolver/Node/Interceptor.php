<?php
namespace Snowdog\Menu\Model\GraphQl\Resolver\Node;

/**
 * Interceptor class for @see \Snowdog\Menu\Model\GraphQl\Resolver\Node
 */
class Interceptor extends \Snowdog\Menu\Model\GraphQl\Resolver\Node implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Snowdog\Menu\Model\GraphQl\Resolver\DataProvider\Node $dataProvider)
    {
        $this->___init();
        parent::__construct($dataProvider);
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
