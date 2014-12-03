<?php

namespace Userguide\Helpers;

use Jig\Utils\FsUtils;
use Jig\Utils\StringUtils;
use Jig\Utils\ArrayUtils;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Indexer
 *
 * @package Userguide
 */
class Indexer
{
    //source file
    const FILE_TOC_YML = 'toc.yml';

    //generated files
    const FILE_MAP_LINKS_FLAT = 'flat-link-map.md';
    const FILE_MAP_LINKS_NESTED = 'nested-link-map.md';
    const FILE_MAP_LINKS = 'link-map.csv';
    const FILE_TOC_JSON = 'toc.json';

    /**
     * @var array
     */
    private $ymlTree = array();

    /**
     * @var array
     */
    private $mdTree = array();


    /**
     * @var array
     */
    private $metaTree = array();

    /**
     * @var array
     */
    private $config = array();

    /**
     * @var string
     */
    private $linkMaps = array('csv' => '', 'flat' => '', 'nested' => '');

    /**
     * @var array
     */
    private $counter = 0;

    /**
     * Create file listing and distribute the actual conversion to the plugins
     *
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {

        $this->config = $config;


        //$this->mdTree = $this->treeFromMd($config['paths']['base'] . $config['paths']['md']);

        // $fileListing = $this->getFileListing($config['paths']['base'] . $config['paths']['md']);

    }

    /**
     * @return array
     */
    public function getYmlTree()
    {
        return $this->ymlTree;
    }

    /**
     * @return array
     */
    public function getMdTree()
    {
        return $this->mdTree;
    }

    /**
     * @return array
     */
    public function getMetaTree()
    {
        return $this->metaTree;
    }

    /**
     * Filters all empty files and directories out
     *
     * @param $inputPath
     * @return array
     */
    protected function getFileListing($inputPath)
    {
    }

    protected function getCounter()
    {
        $this->counter++;
        return str_pad((string)$this->counter, 4, '0', STR_PAD_LEFT);
    }

    public function generateTrees() {
        $treeDir = $this->config['paths']['base'] . $this->config['paths']['trees'];
            $rawYmlTree = Yaml::parse(
            file_get_contents($treeDir . DIRECTORY_SEPARATOR. self::FILE_TOC_YML )
        );
        $this->ymlTree = $this->ymlToTree($rawYmlTree);
        file_put_contents( $treeDir . DIRECTORY_SEPARATOR . self::FILE_TOC_JSON, json_encode( $this->ymlTree ) );
        file_put_contents( $treeDir . DIRECTORY_SEPARATOR . self::FILE_MAP_LINKS, $this->linkMaps['csv'] );
        file_put_contents( $treeDir . DIRECTORY_SEPARATOR . self::FILE_MAP_LINKS_FLAT, $this->linkMaps['flat'] );
        file_put_contents( $treeDir . DIRECTORY_SEPARATOR . self::FILE_MAP_LINKS_NESTED, $this->linkMaps['nested'] );
    }


    /**
     * Add a line of data to the CSV list
     *
     * @param $data
     */
    protected function addToMaps($data)
    {
        $this->linkMaps['csv'] .= implode(',', ArrayUtils::csvQuote(array($data['node-id'], $data['md']))) . "\n";
        $this->linkMaps['flat'] .= '[' . $data['node-id'] . ']: ' . $data['flat'] . "\n";
        $this->linkMaps['nested'] .= '[' . $data['node-id'] . ']: ' . $data['nested'] . "\n";
    }

    /**
     * Helper for ymlToTree()
     *
     * @param $nodeName
     * @param $nodeType
     * @param $nodeId
     * @param string $parentHref
     * @return array
     */
    protected function buildNode($nodeName, $nodeType, $nodeId = '', $parentHref = '')
    {
        $normalized = StringUtils::removeSpecChars($nodeName);
        $node       = array(
            'data'       => $nodeName,
            'type'       => $nodeType,
            'attributes' => array(
                'id'    => StringUtils::removeSpecChars($parentHref . '-' . $normalized),
                'class' => 'node-' . $nodeType,
                'href'  => $parentHref . '/' . $normalized,
            )
        );
        if ($nodeType === 'instance') {
            $node['meta']['node-id'] = $nodeId;
            $node['meta']['md']      = $parentHref . '/' . $normalized . '.md';
            $node['meta']['nested']  = $parentHref . '/' . $normalized . '.html';
            $node['meta']['flat']    = $this->getCounter() . '-' . $normalized . '.html';
        }
        return $node;
    }


    /**
     * Build array based on YML index
     *
     * @param $ymlTree
     * @param string $parentHref
     * @return array
     */
    protected function ymlToTree($ymlTree, $parentHref = '')
    {
        $result = array();
        foreach ($ymlTree as $key => $branch) {

            //files
            if (!empty($branch['title'])) {
                $title                      = $branch['title'];
                $nodeId                     = $branch['id'];
                $node                       = $this->buildNode($title, 'instance', $nodeId, $parentHref);
                $node['attributes']['href'] = $node['meta']['nested'];

                $this->addToMaps($node['meta']);
                $this->metaTree[$node['meta']['node-id']] = $node['meta'];
                unset($node['meta']);
                $result[] = $node;
            } //yaml adds an extra array level with numeric key, so remove this here
            else {
                if (is_numeric($key)) {
                    $key    = key($branch);
                    $branch = current($branch);
                }
                $node             = $this->buildNode($key, 'class', '', $parentHref);
                $node['children'] = $this->ymlToTree($branch, $node['attributes']['href']);
                $result[]         = $node;
            }
        }
        return $result;
    }


    /**
     * Build files based on YML index
     *
     * @param $ymlTree
     * @param string $parentHref
     * @return array
     */
    public function ymlToFiles($ymlTree, $parentHref = '')
    {

        $baseDir = $this->config['paths']['base'] . $this->config['paths']['md'] . 'xyx';
        $result  = array();
        foreach ($ymlTree as $key => $branch) {
            //files
            if (!is_array($branch)) {
                $node    = $this->buildNode($branch, 'instance', $parentHref);
                $mdFile  = basename($node['attributes']['href']) . '.md';
                $content = '# ' . $branch;
                FsUtils::mkDir($baseDir . $parentHref);
                file_put_contents($baseDir . $parentHref . '/' . $mdFile, $content);
            } else {
                //yaml adds an extra array level with numeric key, so remove this here
                if (is_numeric($key)) {
                    $key    = key($branch);
                    $branch = current($branch);
                }
                $node             = $this->buildNode($key, 'class', $parentHref);
                $node['children'] = $this->ymlToFiles($branch, $node['attributes']['href']);
            }
        }
        return $result;
    }


    /**
     * @param $path
     * @param $mdFile
     * @param $pathArr
     * @return mixed
     */
    protected function pathToArrayEntry($path, $mdFile, $pathArr)
    {
        $current = array();
        if (strpos($path, '/') !== false) {
            $current[0] =& $pathArr;
            $i          = 1;
            //create the array name
            $path = explode('/', $path);
            foreach ($path as $key) {
                if (!isset($current[$i - 1][$key])) {
                    $current[$i - 1][$key] = null;
                }
                $current[$i] =& $current[$i - 1][$key];
                $i++;
            }
            //now set the value
            $current[$i - 2][$key] = $mdFile;
            return $pathArr;
        }
        $pathArr[$path] = $mdFile;
        return $pathArr;
    }

    /**
     * @param $mdPath
     * @return array
     */
    public function treeFromMd($mdPath)
    {

        $cwd = getcwd();
        chdir($mdPath);

        $pathArr    = array();
        $rawListing = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator('.'),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($rawListing as $mdFile => $cursor) {
            if ('md' !== $cursor->getExtension()) {
                continue;
            }
            $mdFile  = FsUtils::normalizePath($mdFile);
            $pathArr = $this->pathToArrayEntry($mdFile, basename($mdFile), $pathArr);
        }

        chdir($cwd);
        return $pathArr;
    }

}