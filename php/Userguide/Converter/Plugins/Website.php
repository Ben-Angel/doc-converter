<?php
/**
 * Created by PhpStorm.
 * User: dieter
 * Date: 13/11/14
 * Time: 10:39
 */

namespace Userguide\Converter\Plugins;

use Userguide\Converter\PluginAbstract;
use Userguide\Converter\PluginInterface;
use Jig\Utils\FsUtils;


class Website extends PluginAbstract implements PluginInterface {


    public function runConversion(array $fileListing) {
        foreach($fileListing as $mdFile) {
            $outputPath = $this->getOutputPath(dirname($mdFile));
            FsUtils::mkDir($outputPath);
            system(sprintf('pandoc -f markdown -t html %s > %s', $mdFile, $outputPath . '/' . basename($mdFile, '.md') . '.html'));
        }
    }
} 