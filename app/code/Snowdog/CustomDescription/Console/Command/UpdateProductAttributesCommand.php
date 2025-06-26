<?php
namespace Snowdog\CustomDescription\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Snowdog\CustomDescription\Cron\UpdateProductAttributes as UpdateProductAttributesCron;

class UpdateProductAttributesCommand extends Command
{
    private $cronJob;

    public function __construct(UpdateProductAttributesCron $cronJob)
    {
        $this->cronJob = $cronJob;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('snowdog:update-product-attributes');
        $this->setDescription('Run the UpdateProductAttributes cron job');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting cron job...");
        $this->cronJob->execute();
        $output->writeln("Cron job completed.");
        return 0;
    }
}