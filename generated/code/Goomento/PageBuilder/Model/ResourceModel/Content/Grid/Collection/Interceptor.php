<?php
namespace Goomento\PageBuilder\Model\ResourceModel\Content\Grid\Collection;

/**
 * Interceptor class for @see \Goomento\PageBuilder\Model\ResourceModel\Content\Grid\Collection
 */
class Interceptor extends \Goomento\PageBuilder\Model\ResourceModel\Content\Grid\Collection implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Goomento\PageBuilder\Logger\Logger $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, $mainTable, $eventPrefix, $eventObject, $resourceModel, ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null, ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null, string $model = 'Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\Document', string $type = 'page')
    {
        $this->___init();
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $eventPrefix, $eventObject, $resourceModel, $resource, $connection, $model, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurPage($displacement = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCurPage');
        return $pluginInfo ? $this->___callPlugins('getCurPage', func_get_args(), $pluginInfo) : parent::getCurPage($displacement);
    }
}
