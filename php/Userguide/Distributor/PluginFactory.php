<?php

namespace Userguide\Distributor;

/**
 * Invoke a converter  plugin
 *
 */
class PluginFactory
{

    /**
     * Create an object instance of the plugin
     *
     * @param $targetFormat
     * @param array $paths
     * @param array $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public static function build($targetFormat, $paths, $params = array())
    {
        $nsPlugin = __NAMESPACE__ . '\\Plugins\\' . $targetFormat;
        if (class_exists($nsPlugin)) {
            return new $nsPlugin($paths, $params);
        }
        throw new \Exception('Invalid plugin ' . $nsPlugin);
    }

}
