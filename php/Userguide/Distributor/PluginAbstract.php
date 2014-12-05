<?php

namespace Userguide\Distributor;

use Userguide\Converter\PluginFactory as ConvertorPluginFactory;

abstract class PluginAbstract
{

    protected $paths = array();
    protected $params = array();

    /**
     * @param array $paths
     * @param array $params
     */
    public function __construct($paths, $params = array())
    {
        $this->paths = $paths;
        $this->params = $params;

        /** @var \Userguide\Converter\PluginAbstract $sourcePlugin */

        $sourcePlugin = ConvertorPluginFactory::build($params['params']['source'], $paths );

        $this->paths['source'] = $sourcePlugin->getBaseOutputPath();
    }

}
