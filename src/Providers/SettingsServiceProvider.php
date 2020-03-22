<?php

namespace IbraheemGhazi\SettingsManager\Providers;

use IbraheemGhazi\SettingsManager\SettingsManager;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('settings', function () {
            return new SettingsManager();
        });
		if($this->app->runningInConsole())
			$this->registerMigrations();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

	/**
     * Register migration files.
     *
     * @return void
     */
	protected function registerMigrations(){
		if(!SettingsManager::$ignoreMigration)
			$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

	}


}
