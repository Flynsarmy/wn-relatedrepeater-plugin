<?php

namespace Flynsarmy\RelatedRepeater;

use Backend;
use System\Classes\PluginBase;

/**
 * RelatedRepeater Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'RelatedRepeater',
            'description' => 'A demonstration of using relations with the Reapeater widget',
            'author'      => 'Flyn San',
            'icon'        => 'icon-test'
        ];
    }
}
