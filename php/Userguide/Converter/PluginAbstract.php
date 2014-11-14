<?php

namespace Userguide\Converter;

use Jig\Utils\FsUtils;

/**
 * PluginBase is the abstract base class for all plugins
 *
 * @author Dieter Raber <dieter@taotesting.com>
 */
abstract class PluginAbstract
{

    protected $paths = array();

    /**
     * Create and change to the output directory
     *
     * @param $paths
     */
    public function __construct($paths)
    {
        $paths['out'] .= '/' . strtolower(basename(FsUtils::normalizePath(get_called_class())));
        FsUtils::mkDir($paths['base'] . $paths['out']);
        $this->paths = $paths;
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

    protected function getOutputPath($path, $style='nested') {
        switch($style) {
            case 'nested':
                return str_replace($this->paths['base'] . $this->paths['in'], $this->paths['base'] . $this->paths['out'], $path);
        }
    }

}
