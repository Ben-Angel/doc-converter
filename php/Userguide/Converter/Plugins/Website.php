<?php
/**
 * Created by PhpStorm.
 * User: dieter
 * Date: 13/11/14
 * Time: 10:39
 */

namespace Userguide\Converter\Plugins;

use Jig\Utils\Console;
use Userguide\Converter\PluginAbstract;
use Userguide\Converter\PluginInterface;
use Jig\Utils\FsUtils;
use Userguide\Helpers\Indexer;


class Website extends PluginAbstract implements PluginInterface {


    public function runConversion(array $fileListing) {
        foreach($fileListing as $mdFile) {
            $outputPath = $this->getOutputPath(dirname($mdFile));
            FsUtils::mkDir($outputPath);
            system(sprintf('%s -f markdown -t html %s > %s',
                $this->options['bin'],
                $mdFile . ' ' . $this->paths['base'] . $this->paths['trees'] . DIRECTORY_SEPARATOR . Indexer::FILE_MAP_LINKS_NESTED,
                $outputPath . '/' . basename($mdFile, '.md') . '.html'), $retVal);
        }
    }
} 