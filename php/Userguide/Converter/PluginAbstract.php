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
    protected $executable = null;

    /**
     * Create and change to the output directory
     *
     * @param array $paths
     * @param null|string $executable
     */
    public function __construct($paths, $executable = null)
    {
        $paths['distro'] .= FsUtils::normalizePath('/' . strtolower(basename( str_replace( '\\', DIRECTORY_SEPARATOR, get_called_class() ) )));
        $this->paths = $paths;
        $this->executable = $executable;
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
                return str_replace($this->paths['base'] . $this->paths['md'], $this->paths['base'] . $this->paths['distro'], $path);
        }
    }

}
