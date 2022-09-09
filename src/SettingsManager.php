<?php


namespace IbraheemGhazi\SettingsManager;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\Macroable;

class SettingsManager
{
    use Macroable;

    /** @var string which model is used to save entries */
    public static $model = Setting::class;

    /** @var bool when ever set a new value to an entry using set($key,$value), should save it to DB */
    public static $autoSaveOnSet = TRUE;

    /** @var bool when ever set a new value to an entry using set($key,$value), should create it in DB if not exists */
    public static $autoCreateOnSet = TRUE;


    /** @var bool control auto loading entries from database when application booted */
    public static $autoLoadFromDatabase = TRUE;

//    protected $applyBindingOnNonExists = TRUE;

    protected $configBindings = [];

    /** @var Collection|Model $cache */
    private $cache;

    /** @var bool $ignoreMigration */
    public static $ignoreMigration = false;

    public function __construct()
    {
        $this->cache = collect();
        App::booted(function () {
            if (static::$autoLoadFromDatabase) {
                $this->load();
            }
        });

        $this->getModel()->saved(function (Model $entry) {
            $this->applyBindingOnModel($entry);
        });
    }

    /**
     * load all entries from database then apply bindings if required
     * @param bool $force force loading data from database even it's already loaded
     * @return $this
     */
    public function load($force = false)
    {
        $execute = function () use ($force) {
            if ($this->cache->isNotEmpty() && !$force) return;
            $this->cache = $this->getModel()->get();
            foreach ($this->cache as $entry) {
                $this->applyBindingOnModel($entry);
            }
        };

        /**
         * if we are on PHPUnit never run load() before tables exists
         * @since 5.8.16
         */
        if (app()->runningUnitTests()) {
            app('events')->listen(MigrationsEnded::class, $execute);
        } else {
            $execute();
        }
        return $this;
    }

    /**
     * get settings model instance
     * @return Model
     */
    protected function getModel()
    {
        return $this->resolve(static::$model);
    }

    /**
     * resolve class string path to instance
     * @param $class
     * @return mixed
     */
    private function resolve($class)
    {
        return App::make($class);
    }

    /**
     * apply bindings using entry key and value
     * @param Model $entry
     */
    protected function applyBindingOnModel(Model $entry)
    {
        $this->applyBindingOn($this->getBinding($entry), $entry->getAttribute('value'));
    }

    /**
     * apply bindings using key and value
     * @param string $key
     * @param $value
     */
    protected function applyBindingOn($key, $value)
    {
        if (is_string($key) && strlen($key) && array_key_exists($key, $this->configBindings)) {
            $config_key = $this->configBindings[$key];
            config([$config_key => $this->tryDecodeValue($value)]);
        }
    }

    protected function getBinding(Model $entry)
    {
        return $this->configBindings[$entry->getAttribute('key')] ?? NULL;
    }

    /**
     * change the Eloquent Model used to handle database connection
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        static::$model = $model;

        return $this;
    }


    /**
     * get all loaded settings
     */
    public function all()
    {
        return $this->cache->pluck('value', 'key');
    }

    /**
     * get specific settings entry or return default if not found
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = NULL)
    {
        if ($entry = $this->getEntry($key)) {
            return $this->tryDecodeValue( $entry->getAttribute('value') );
        }
//        if($this->applyBindingOnNonExists){
//            $this->applyBindingOn($key, $default);
//        }
        return $default;
    }

    /**
     * try to decode the input value, if it has any error
     * while decoding it will return the input value.
     * @param $value
     * @return mixed
     */
    protected function tryDecodeValue($value){
        try {
            $temp = json_decode($value, true);
            if(json_last_error() === JSON_ERROR_NONE){
                return $temp;
            }
            return $value;
        } catch (\Exception $ex) {
            return $value;
        }
    }

    /**
     * get Model entry from already loaded settings using key
     * @param $key
     * @return Model|null
     */
    protected function getEntry($key)
    {
        return $this->cache->where('key', $key)->first();
    }

    /**
     * set specific settings entry and save it if required
     * @param $key
     * @param $value
     * @param bool $save force saving this settings entry (update or create if $should_create == TRUE)
     * @param bool $should_create when forcing save entry, should create it if not exists ?
     * @return $this
     */
    public function set($key, $value, $save = false, $should_create = true)
    {
        $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        if ($entry = $this->getEntry($key)) {
            $entry->setAttribute('value', $value);
            if (static::$autoSaveOnSet || $save) {
                $entry->save();
            }
            return $this;
        }

        tap($this->getModel()->newInstance(), function (Model $entry) use ($key, $value, $save, $should_create) {
            $entry->setAttribute('key', $key);
            $entry->setAttribute('value', $value);
            if (static::$autoCreateOnSet || ($save && $should_create)) {
                $entry->save();
            }
            $this->cache->push($entry);
        });

        return $this;
    }

    /**
     * forget a key from settings
     * @param string $key setting key to delete
     * @param bool $permanent_remove determine if should also remove DB entry
     * @param null|mixed $callback once forgot entry, if has binding this will it's value
     * @return $this
     */
    public function forget($key, $permanent_remove = true, $callback = NULL)
    {
        $entry = $this->getEntry($key);

        if (!$entry) return $this;

        $this->removeEntry($entry);
        $permanent_remove && rescue(function () use ($entry) {
            $entry->exists && $entry->delete();
        });

        if ($this->hasBinding($entry) && !is_null($callback)) {
            $config_key = $this->getBinding($entry);
            $config_key && $this->applyBindingOn($config_key, value($callback));
        }

        return $this;
    }

    protected function removeEntry(Model $entry)
    {
        if ($key = $this->cache->search($entry) !== FALSE) {
            $this->cache->forget($key);
        }
    }

    protected function hasBinding(Model $entry)
    {
        return array_key_exists($entry->getAttribute('key'), $this->configBindings);
    }

    /**
     * get current registered config bindings
     * @return array
     */
    public function getBindings()
    {
        return $this->configBindings;
    }

    /**
     * bind settings to config, so when ever the settings key is
     * retrieved or saved it will change the assigned this setting
     * value to default app configuration.<br><br>
     * example 1: <code>bind('app.name')</code> will result to override <code>config('app.name')</code> to have <code>get('app.name')</code> value
     * example 2: <code>bind('app.name','app_name')</code> will result to override <code>config('app.name')</code> to have <code>get('app_name')</code> value
     * @param string $settings_key
     * @param string|null $config_key
     * @return $this
     */
    public function bind(string $settings_key, ?string $config_key = NULL)
    {
        $this->configBindings[$settings_key] = $config_key ?: $settings_key;
        return $this;
    }

    /**
     * unbind already bound settings to config link
     * @param $settings_key
     * @return $this
     */
    public function unbind($settings_key)
    {
        Arr::forget($this->configBindings, $settings_key);
        return $this;
    }


}
