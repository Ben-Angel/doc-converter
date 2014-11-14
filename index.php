<?php
require 'php/vendor/autoload.php';
use Userguide\Converter\ConverterTask;

new ConverterTask(__DIR__ . '/config/config.yml');
