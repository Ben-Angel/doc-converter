<?php
/**
 * Created by PhpStorm.
 * User: dieter
 * Date: 17/11/2014
 * Time: 21:49
 */

namespace Userguide\Helpers;


class Base {

    public static function getMdTitle($path) {
        $contentArr = FsUtils::file($path);
        $firstLine  = count($contentArr) ? trim(array_shift($contentArr)) : '';
        return 0 === strpos($firstLine, '#') ? ltrim($firstLine, '# ') : '';
    }
} 