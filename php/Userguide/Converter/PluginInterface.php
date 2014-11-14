<?php

namespace Userguide\Converter;

/**
 * PluginInterface
 *
 * @author Dieter Raber <dieter@taotesting.com>
 */
interface PluginInterface
{

    /**
     * Run the actual conversion from Markdown to target format
     *
     * @param array $fileListing
     * @return mixed
     */
    public function runConversion(array $fileListing);

}
