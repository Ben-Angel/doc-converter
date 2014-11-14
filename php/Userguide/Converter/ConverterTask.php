<?php

namespace Userguide\Converter;

use Jig\Utils\FsUtils;
use Symfony\Component\Yaml\Yaml;
use Userguide\Converter\PluginFactory;

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
        ob_start();
        include($configFile);
        $config = Yaml::parse(ob_get_clean());

        foreach($config['paths'] as &$path) {
            $path = FsUtils::normalizePath($path);
        }

        if(!is_dir($config['paths']['base'] . $config['paths']['in'])){
            throw new \Exception('Markdown files not found');
        }

        $fileListing = $this->getFileListing($config['paths']['base'] . $config['paths']['in']);

        foreach($config['targets'] as $targetFormat) {
            $targetPlugin = PluginFactory::build($targetFormat, $config['paths']);
            $targetPlugin->runConversion($fileListing);
        }
    }

    /**
     * Filters all empty files and directories out
     *
     * @param $inputPath
     * @return array
     */
    protected function getFileListing($inputPath) {

        $listing = array();
        $rawListing = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($inputPath), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($rawListing as $mdFile => $cursor) {
            if ('md' !== $cursor->getExtension()) {
                continue;
            }
            $listing[] = $mdFile;
        }
        return $listing;
    }

}