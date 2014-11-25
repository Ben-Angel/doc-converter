<?php
/**
 * Created by PhpStorm.
 * User: dieter
 * Date: 13/11/14
 * Time: 11:56
 */

namespace Userguide\Distributor;

use Userguide\Helpers\Config;

class DistributorTask
{

    function __construct( $configFile )
    {
        $config = Config::get( $configFile );

        foreach ($config['platforms'] as $targetSystem) {
            $targetPlugin = PluginFactory::build( $targetSystem['name'], $config['paths'], $targetSystem['params'] );
            $targetPlugin->execute();
        }

    }
}