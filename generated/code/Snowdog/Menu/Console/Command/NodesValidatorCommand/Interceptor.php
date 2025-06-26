<?php
namespace Snowdog\Menu\Console\Command\NodesValidatorCommand;

/**
 * Interceptor class for @see \Snowdog\Menu\Console\Command\NodesValidatorCommand
 */
class Interceptor extends \Snowdog\Menu\Console\Command\NodesValidatorCommand implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Snowdog\Menu\Api\MenuRepositoryInterface $menuRepository, \Snowdog\Menu\Api\NodeRepositoryInterface $nodeRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Snowdog\Menu\Model\ImportExport\Processor\Import\Node\Validator $validator, \Snowdog\Menu\Model\ImportExport\Processor\Import\Validator\ValidationAggregateError $validationAggregateError, \Magento\Framework\App\State $state, \Snowdog\Menu\Model\ImportExport\Processor\Import\Node\Validator\TreeTrace $treeTrace, ?string $name = null)
    {
        $this->___init();
        parent::__construct($menuRepository, $nodeRepository, $searchCriteriaBuilder, $validator, $validationAggregateError, $state, $treeTrace, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function run(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'run');
        return $pluginInfo ? $this->___callPlugins('run', func_get_args(), $pluginInfo) : parent::run($input, $output);
    }
}
