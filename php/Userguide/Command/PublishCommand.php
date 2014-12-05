<?php

namespace Userguide\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Userguide\Distributor\PluginFactory;
use Userguide\Helpers\Config;


class PublishCommand extends Command
{
    const ALL = 'All';

    private $config = array();

    protected function configure()
    {
        $this
            ->setName( 'publish' )
            ->setDescription( 'Push data to certain server type' )
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'What target server type is?'
            )
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

        $type = ucfirst( $input->getArgument( 'type' ) );

        $allowedPlatforms = $this->getAllowedPlatforms();

        if ($type && in_array( $type, $allowedPlatforms )) {
            $targetSystem = $this->config['platforms'][array_search( $type, $allowedPlatforms )];
            $targetPlugin = PluginFactory::build(
                $targetSystem['name'],
                $this->config['paths'],
                $targetSystem['params']
            );
            $targetPlugin->execute();
        } else {
            $output->writeln( '<error>Please specify destination server type</error>' );
        }

    }

    protected function getAllowedPlatforms()
    {
        return array_map(
            function ( $e ) {
                return $e['name'];
            },
            $this->config['platforms']
        );
    }
}