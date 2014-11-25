<?php

namespace Userguide\Converter;

use Jig\Utils\Console;
use Userguide\Helpers\Config;
use Userguide\Helpers\Indexer;

/**
 * Class Converter
 * Convert documentation written in Markdown to other formats
 *
 * @package Userguide
 */
class ConverterTask
{


    /**
     * Create file listing and distribute the actual conversion to the plugins
     *
     * @param $configFile
     * @throws \Exception
     */
    public function __construct($configFile)
    {
        $config = Config::get($configFile);

        if(!is_dir($config['paths']['base'] . $config['paths']['md'])){
            throw new \Exception('Markdown files not found');
        }

        $indexer = new Indexer($config);

        Console::log($indexer -> getYmlTree(), $indexer->treeFromMd($config['paths']['base'] . $config['paths']['md']));

//
       foreach($config['targets'] as $targetFormat) {
           $targetPlugin = PluginFactory::build($targetFormat['name'], $config['paths'], $targetFormat['bin']);
           $targetPlugin->runConversion($fileListing);
       }
    }

    /**
     * Filters all empty files and directories out
     *
     * @param $inputPath
     * @return array
     */
    protected function getFileListing($inputPath)
    {

        $listing    = array();
        $rawListing = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($inputPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($rawListing as $mdFile => $cursor) {
            if ('md' !== $cursor->getExtension()) {
                continue;
            }
            $listing[] = $mdFile;
        }
        return $listing;
    }
}