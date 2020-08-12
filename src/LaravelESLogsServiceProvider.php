<?php

namespace Twentysix22\LaravelESLogs;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\ServiceProvider;
use Twentysix22\LaravelESLogs\Commands\Configure;
use Twentysix22\LaravelESLogs\Commands\Tidy;
use Twentysix22\LaravelESLogs\Services\Logging\Jobs\LogJob;
use Twentysix22\LaravelESLogs\Services\Logging\LogListener;
use Twentysix22\LaravelESLogs\Services\Logging\Manager;

class LaravelESLogsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->publishes([
            __DIR__.'/../config/laraveleslogs.php' => config_path('laraveleslogs.php'),
        ]);

        if (config('laraveleslogs.jobs')) {
            $this->app['events']->subscribe(LogJob::class);
            $this->app['events']->listen(MessageLogged::class, LogListener::class);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        if (config('laraveleslogs.jobs')) {
            $this->app->singleton('logging.jobs', Manager::class);
            $this->app->tag('logging.jobs', Manager::CONTAINER_TAG);
        }

        if (config('laraveleslogs.requests')) {
            $this->app->singleton('logging.requests', Manager::class);
            $this->app->tag('logging.requests', Manager::CONTAINER_TAG);
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Registering package commands.
        $this->commands([
            Configure::class,
            Tidy::class,
        ]);
    }
}
