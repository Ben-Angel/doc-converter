<?php

namespace Userguide\Converter;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
        $indexer->generateTrees();

        $fileListing = $this->getFileListing($config['paths']['base'] . $config['paths']['md'], $indexer->getMetaTree());

        foreach($config['targets'] as $targetFormatOptions) {
           $targetPlugin = PluginFactory::build($targetFormatOptions['name'], $config['paths'], $targetFormatOptions, $indexer);
           $targetPlugin->runConversion($fileListing);
        }
    }

    /**
     * Filters all empty files and directories out
     *
     * @param $inputPath
     * @param $metaTree
     *
     * @return array
     * @throws \Exception
     */
    protected function getFileListing($inputPath, $metaTree)
    {
        $listing = array();
        $finder = new Finder();
        $files  = $finder->files()->name( '*.md' )->in( $inputPath );
        $metaTree = array_map(function($e){return trim($e['md'],DIRECTORY_SEPARATOR);},$metaTree);
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $nodeId = array_search( $file->getRelativePathname(), $metaTree );
            if ($nodeId === false){
                $error = sprintf( '%s don\'t match to file structure, cant find %s ', Indexer::FILE_TOC_YML, $file->getRelativePathname() );
                throw new \Exception( $error );
            }

            $listing[$nodeId] = $file->getRealPath();
        }

        return $listing;
    }
}