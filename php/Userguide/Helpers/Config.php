<?php
namespace Userguide\Helpers;

use Jig\Utils\FsUtils;
use Symfony\Component\Yaml\Yaml;

class Config
{

    private static $configs = array();

    public static function get( $configFile )
    {
        if ( ! file_exists( $configFile )) {
            throw new \Exception( 'No config file found' );
        }

        if ( ! isset( self::$configs[$configFile] )) {

            ob_start();
            include( $configFile );
            $config = Yaml::parse( ob_get_clean() );

            foreach ($config['paths'] as &$path) {
                $path = FsUtils::normalizePath( $path );
            }

            self::$configs[$configFile] = $config;
        }

        return self::$configs[$configFile];
    }
}