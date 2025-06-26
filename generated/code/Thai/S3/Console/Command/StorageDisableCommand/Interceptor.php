<?php
namespace Thai\S3\Console\Command\StorageDisableCommand;

/**
 * Interceptor class for @see \Thai\S3\Console\Command\StorageDisableCommand
 */
class Interceptor extends \Thai\S3\Console\Command\StorageDisableCommand implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\State $state, \Magento\Config\Model\Config\Factory $configFactory)
    {
        $this->___init();
        parent::__construct($state, $configFactory);
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
