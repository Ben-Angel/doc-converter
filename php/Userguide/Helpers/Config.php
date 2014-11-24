<?php
namespace Userguide\Helpers;

use Jig\Utils\FsUtils;
use Symfony\Component\Yaml\Yaml;

class Config
{

    public static function get( $configFile )
    {
        if ( ! file_exists( $configFile )) {
            throw new \Exception( 'No config file found' );
        }

        ob_start();
        include( $configFile );
        $config = Yaml::parse( ob_get_clean() );

        foreach ($config['paths'] as &$path) {
            $path = FsUtils::normalizePath( $path );
        }

        return $config;
    }
}