<?php

namespace TsfCorp\Graylog;

use Illuminate\Support\ServiceProvider;
use TsfCorp\Graylog\Commands\GraylogCommand;
use TsfCorp\Graylog\Commands\InstallCommand;

class GraylogServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app->runningInConsole())
        {
            $this->publishes([
                __DIR__ . '/../config/graylog.php' => config_path('graylog.php')
            ], 'graylog-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations')
            ], 'graylog-migrations');

            $this->commands([
                InstallCommand::class,
                GraylogCommand::class,
            ]);
        }
    }

    public function provides()
    {
        return ['graylog'];
    }
}