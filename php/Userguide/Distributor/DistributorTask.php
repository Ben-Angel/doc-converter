<?php
/**
 * Created by PhpStorm.
 * User: dieter
 * Date: 13/11/14
 * Time: 11:56
 */

namespace Userguide\Distributor;

use Userguide\Converter\TaskInterface;
use Userguide\Helpers\Config;

class DistributorTask implements TaskInterface
{
    /**
     * @var array
     */
    protected $config;

    function __construct( $configFile )
    {
        $this->config = Config::get( $configFile );
    }

    public function run()
    {
        foreach ($this->config['platforms'] as $targetSystem) {
            $targetPlugin = PluginFactory::build( $targetSystem['name'], $this->config['paths'], $targetSystem['params'] );
            $targetPlugin->execute();
        }
    }
}