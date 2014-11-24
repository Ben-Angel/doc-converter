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
     * @param array $paths
     * @param null|string $executable
     *
     * @throws \Exception
     * @return mixed
     */
    public static function build($targetFormat, $paths, $executable = null)
    {
        $nsPlugin = __NAMESPACE__ . '\\Plugins\\' . $targetFormat;
        if (class_exists($nsPlugin)) {
            return new $nsPlugin($paths, $executable);
        }
        throw new \Exception('Invalid plugin ' . $nsPlugin);
    }

}
