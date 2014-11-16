<?php

namespace Userguide\Converter;

use Jig\Utils\FsUtils;
use Jig\Utils\StringUtils;
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


        $ymlTree = Yaml::parse(
            file_get_contents($config['paths']['base'] . $config['paths']['trees'] . '/toc.yml')
        );

        $tree             = $this->buildNode('Table of Contents', 'class');
        $tree['children'] = $this->buildTree($ymlTree);


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

    /**
     * @param $nodeName
     * @param $nodeType
     * @param string $parentHref
     * @return array
     */
    protected function buildNode($nodeName, $nodeType, $parentHref='')
    {
        $normalized = StringUtils::removeSpecChars($nodeName);
        $node       = array(
            'data'       => $nodeName,
            'type'       => $nodeType,
            'attributes' => array(
                'id'    => $normalized,
                'class' => 'node-' . $nodeType,
                'href'  => $parentHref . '/' . $normalized,
            )
        );
        return $node;
    }
    

    /**
     * @param $ymlTree
     * @param string $parentHref
     * @return array
     */
    protected function buildTree($ymlTree, $parentHref = '')
    {
        $result = array();
        foreach ($ymlTree as $key => $branch) {
            //files
            if (!is_array($branch)) {
                $result[] = $this->buildNode($branch, 'instance', $parentHref);
            } else {
                //yaml adds an extra array level with numeric key, so remove this here
                if (is_numeric($key)) {
                    $key    = key($branch);
                    $branch = current($branch);
                }
                $node             = $this->buildNode($key, 'class', $parentHref);
                $node['children'] = $this->buildTree($branch, $node['attributes']['href']);
                $result[]         = $node;
            }
        }
        return $result;
    }

}
