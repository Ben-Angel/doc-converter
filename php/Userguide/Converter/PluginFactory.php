<?php

namespace Userguide\Converter;

use Userguide\Helpers\Indexer;

/**
 * Invoke a converter  plugin
 *
 * @author Dieter Raber <dieter@taotesting.com>
 */
class PluginFactory
{

    /**
     * Create an object instance of the plugin
     *
     * @param $targetFormat
     * @param array $paths
     * @param array $options
     *
     * @param Indexer $indexer
     *
     * @return PluginAbstract|PluginInterface
     * @throws \Exception
     */
    public static function build( $targetFormat, array $paths, array $options = array(), Indexer $indexer = null )
    {
        $nsPlugin = __NAMESPACE__ . '\\Plugins\\' . $targetFormat;
        if (class_exists($nsPlugin)) {
            return new $nsPlugin($paths, $options, $indexer);
        }
        throw new \Exception('Invalid plugin ' . $nsPlugin);
    }

}