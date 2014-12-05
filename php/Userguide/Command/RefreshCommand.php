<?php

namespace Userguide\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Userguide\Distributor\PluginFactory;
use Userguide\Helpers\Config;
use Userguide\Helpers\Indexer;


class RefreshCommand extends Command
{
    const ALL = 'All';

    private $config = array();

    protected function configure()
    {
        $this
            ->setName( 'reindex' )
            ->setDescription( 'Rebuilds tree, allow you to add media to  ' )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_OPTIONAL,
                'set custom config file, relative to application root dir',
                '/../../../config/config.yml'
            );

    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->config = Config::get( __DIR__ . $input->getOption('config') );

        $indexer = new Indexer($this->config);
        $indexer->generateTrees();

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln('done');
        }

    }
}