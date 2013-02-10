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

class PacketCommand extends AbstractLiveSyncCommand
{
    /** @var InputInterface */
    protected $_input;

    /** @var OutputInterface */
    protected $_output;

    protected function configure()
    {
        $this
            ->setName('livesync:packet')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, "A filter to only process packets that match")
            ->addArgument('source', InputArgument::OPTIONAL, "The source magento instance to pull packets from")
            ->setDescription('Lists out packets for debugging')
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
        $this->_output = $output;

        $this->detectMagento($output);
        $this->initMagento();

        $import = $this->getImportModel()
            ->setMagentoSource($this->_getSourceArgument());
        $files = $import->getFiles();
        foreach ($files as $file) {
            if ($this->_isFileFilteredOut($file)) {
                continue;
            }

            $output->writeln("<info>$file</info>");
            $this->_outputPacket($file);
        }
    }

    protected function _outputPacket($file)
    {
        $json = file_get_contents($file);
        $pretty = \Zend_Json::prettyPrint($json);

        $this->_output->writeln($pretty);
    }

    protected function _getSourceArgument()
    {
        $source = parent::_getSourceArgument();
        if (!$source) {
            $source = $this->_magentoRootFolder;
        }

        return $source;
    }
}