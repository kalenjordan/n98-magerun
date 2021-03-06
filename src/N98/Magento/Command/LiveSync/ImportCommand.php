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

class ImportCommand extends AbstractLiveSyncCommand
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

    protected function initMagento()
    {
        if ($this->_magentoRootFolder !== null) {
            if ($this->_magentoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
                require_once $this->_magentoRootFolder . '/app/bootstrap.php';
            } else {
                require_once $this->_magentoRootFolder . '/app/Mage.php';
            }
            \Mage::app('', 'store');
            return true;
        }

        return false;
    }
}