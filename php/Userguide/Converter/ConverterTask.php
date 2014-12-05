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
class ConverterTask implements TaskInterface
{
    protected $config;

    /**
     * Create file listing and distribute the actual conversion to the plugins
     *
     * @param $configFile
     * @throws \Exception
     */
    public function __construct($configFile)
    {
        $this->config = Config::get($configFile);

        if(!is_dir($this->config['paths']['base'] . $this->config['paths']['md'])){
            throw new \Exception('Markdown files not found');
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

    public function run()
    {
        $indexer = new Indexer($this->config);
        $indexer->generateTrees();

        $fileListing = $this->getFileListing($this->config['paths']['base'] . $this->config['paths']['md'], $indexer->getMetaTree());

        foreach($this->config['targets'] as $targetFormatOptions) {
            $targetPlugin = PluginFactory::build($targetFormatOptions['name'], $this->config['paths'], $targetFormatOptions, $indexer);
            $targetPlugin->runConversion($fileListing);
        }
    }
}