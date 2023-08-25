<?php

namespace IbraheemGhazi\SettingsManager\Facades;

use IbraheemGhazi\SettingsManager\SettingsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Settings\SettingsManager
 * @method static SettingsManager load($force = false)
 * @method static SettingsManager refreshBindings()
 * @method static SettingsManager setModel(Model $model)
 * @method static Collection all()
 * @method static mixed|null get($key, $default = NULL)
 * @method static SettingsManager set($key, $value, $save = false, $should_create = true)
 * @method static SettingsManager forget($key, $permanent_remove = true, $callback = NULL)
 * @method static array getBindings()
 * @method static SettingsManager bind(string $settings_key, ?string $config_key = NULL)
 * @method static SettingsManager unbind($settings_key)
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
