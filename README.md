Laravel Settings Manager
==========

This is a Laravel package provide an easy way to control application settings, which can easily integrated to application configuration to be used with config() function


Compatibility
============
This package was built for versions >= 7.0 but also compatible with versions >= 5.6

Features
========

* auto load all settings (with option to disable it)
* cache loaded entries for current request
* auto save entries to DB (with option to disable it)
* auto create entries to DB if not exists (with option to disable it)
* map setting entry from DB to config() key
* changeable model.
* customizable package configuration with manager.
* macroable settings manager
* compatible with PHPUnit testings

Installation
==============

```
composer require ibraheem-ghazi/laravel-settings-manager
```
then:
```
php artisan migrate
```
if your installed laravel version does not support auto discover packages then:

1- add this provider to config:

```
IbraheemGhazi\SettingsManager\Providers\SettingsServiceProvider::class,
```

2- then add alias:
```
'Settings' => IbraheemGhazi\SettingsManager\Facades\Settings::class,
```


Configuration
================

### Attributes
|func                              | description|
|----------------------------------|----------------------|
|$ignoreMigration                   | ignore auto register package migrations.
|$AutoLoadFromDatabase           | disable/enable auto loading configuration from database.
|$AutoSaveOnSet 	               | disable/enable auto save configuration to database.
|$AutoCreateOnSave        	   | disable/enable auto create configuration to database if not already exists.
|$Model			        	   | change the model used to save settings - must have key, value fields where key is *primary key*.

### Methods
|func                              | params         | description|
|----------------------------------|----------------|----------------------|
|bind				        	   | string $settings_key, ?string $config_key = NULL   | bind a settings entry from DB to application configuration key
|unbind				               | string $settings_key | remove binding of a settings entry to application configuration key

Other Available Functions
=========================

|func                    | params        	   | return        | description|
|------------------------|---------------------|---------------|----------------------|
|load				     | bool $force = false | ($this)       | load settings from DB (or force reload it)
|getModel                | -                   | Model         | return the model used to control DB entries
|getBindings    		 | -                   | Collection    | get all configured settings to configurations bindings
|all                	 | -                   | Collection    | return collection of strings of all entries.
|get    			     | $key, $default=NULL | mixed         | return the value of specified key
|set    			     | $key, $value, $save = false, $should_create = true | ($this)         | set the value for specified key, (with option to force enable/disable saving or creating)
|forget    			     | $key, $permanent_remove = true, $callback = NULL   | ($this)         | remove the specified key, (with option to force enable/disable removing entry from DB, and call a callback function when done removing)

