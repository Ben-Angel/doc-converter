#!/usr/bin/env php
<?php
require __DIR__ . '/../php/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Userguide\Command\PublishCommand;
use Userguide\Command\RefreshCommand;

$application = new Application();
//$application->add( new PublishCommand() );
$application->add( new RefreshCommand() );
$application->run();