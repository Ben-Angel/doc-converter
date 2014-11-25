<?php

namespace Userguide\Distributor;

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
     * @return mixed
     */
    public function execute();

}
