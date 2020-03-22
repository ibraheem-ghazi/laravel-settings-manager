<?php

namespace IbraheemGhazi\SettingsManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Settings\SettingsManager
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'settings';
    }
}
