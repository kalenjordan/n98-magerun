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
    /** @var InputInterface */
    protected $_input;

    protected function configure()
    {
        $this
            ->setName('livesync:import')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, "A filter to only process packets that match")
            ->addArgument('source', InputArgument::REQUIRED, "The source magento instance to import from")
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

        $import = $this->getImportModel()
            ->setMagentoSource($this->_getSourceArgument());
        $files = $import->getFiles();
        foreach ($files as $file) {
            if ($this->_isFileFilteredOut($file)) {
                continue;
            }

            $output->writeln("<info>Processing $file");
            $import->processPacket($file);
        }
    }

    protected function _isFileFilteredOut($file)
    {
        if (! $this->_input->getOption('filter')) {
            return false;
        }

        $filter = $this->_input->getOption('filter');
        $filter = str_replace("/", "\/", $filter);
        if (preg_match('/' . $filter . '/', $file, $match)) {
            return false;
        }

        return true;
    }

    protected function _getSourceArgument()
    {
        $source = $this->_input->getArgument('source');
        if (substr($source, -1) == '/') {
            $source = substr($source, 0, strlen($source) - 1);
        }

        return $source;
    }

    /**
     * @return \KJ_LiveSync_Model_Import
     */
    protected function getImportModel()
    {
        if (!class_exists('KJ_LiveSync_Model_Import')) {
            throw new \Exception("Looks like you haven't installed the KJ_LiveSync module yet. \r\nIt needs to be installed in both the target and source Magento instances.");
        }

        return \Mage::getModel('livesync/import');
   }
}