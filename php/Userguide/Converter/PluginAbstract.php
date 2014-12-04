<?php

namespace Userguide\Converter;

use Jig\Utils\FsUtils;
use Userguide\Helpers\Indexer;

/**
 * PluginBase is the abstract base class for all plugins
 *
 * @author Dieter Raber <dieter@taotesting.com>
 */
abstract class PluginAbstract
{

    protected $paths = array();
    protected $options;
    protected $indexer;

    /**
     * Create and change to the output directory
     *
     * @param array $paths
     * @param array $options
     * @param Indexer $indexer
     *
     */
    public function __construct($paths, array $options, Indexer $indexer)
    {
        $this->paths = $paths;
        $this->options = $options;
        $this->indexer = $indexer;
        $this->paths['distro'] .= FsUtils::normalizePath('/' . strtolower(basename( str_replace( '\\', DIRECTORY_SEPARATOR, get_called_class() ) )));
    }

    /**
     * Get the first heading of an md file
     *
     * @param $path
     * @return string
     */
    protected function getTitle($path)
    {
        $contentArr = FsUtils::file($path);
        $firstLine  = count($contentArr) ? trim(array_shift($contentArr)) : '';
        return 0 === strpos($firstLine, '#') ? ltrim($firstLine, '# ') : '';
    }

    /**
     * @param $path
     * @param string $style
     * @return mixed
     */
    protected function getOutputPath($path, $style='nested') {
        switch($style) {
            case 'nested':
                return str_replace($this->paths['base'] . $this->paths['md'], $this->paths['base'] . $this->paths['distro'], $path);
        }
    }

    /**
     * @return string
     */
    public function getBaseOutputPath(){
        return $this->paths['base'] . $this->paths['distro'];
    }

    /**
     * Directory with images, styles etc specific for plugin
     * @return string
     */
    protected function getAssetsDir(){
        return $this->paths['base'] . $this->paths['assets'] . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected function getResourceDir(){
        return $this->paths['base'] . $this->paths['resources'] . DIRECTORY_SEPARATOR . $this->options['name'] . DIRECTORY_SEPARATOR;
    }


}
