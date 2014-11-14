<?php

namespace Userguide\Converter;

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
     * @param $paths
     * @return mixed
     * @throws \Exception
     */
    public static function build($targetFormat, $paths)
    {
        $nsPlugin = __NAMESPACE__ . '\\Plugins\\' . $targetFormat;
        if (class_exists($nsPlugin)) {
            return new $nsPlugin($paths);
        }
        throw new \Exception('Invalid plugin ' . $nsPlugin);
    }

}
