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

class AbstractLiveSyncCommand extends AbstractMagentoCommand
{
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