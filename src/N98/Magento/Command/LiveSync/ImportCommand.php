<?php

namespace N98\Magento\Command\LiveSync;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Cache\ClearCommand as ClearCacheCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

class ImportCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('livesync:import')
            ->setDescription('Imports packets from a Magento instance using KJ_LiveSync module')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;

        $this->detectMagento($output);
        $this->initMagento();

        $output->writeln("<info>Running LiveSync import</info>");

        $import = $this->getImportModel();
        $import->run();
    }

    protected function getImportModel()
    {
        if (!class_exists('KJ_LiveSync_Model_Import')) {
            throw new \Exception("Looks like you haven't installed the KJ_LiveSync module yet. \r\nIt needs to be installed in both the target and source Magento instances.");
        }

        return Mage::getModel('livesync/import');
   }
}