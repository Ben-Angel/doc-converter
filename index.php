<?php
require 'php/vendor/autoload.php';
use Userguide\Converter\ConverterTask;
use Userguide\Distributor\DistributorTask;

( new ConverterTask( __DIR__ . '/config/config.yml' ) )->run();
//( new DistributorTask( __DIR__ . '/config/config.yml' ) )->run();
