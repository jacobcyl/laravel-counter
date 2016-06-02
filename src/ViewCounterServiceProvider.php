<?php
namespace Jacobcyl\ViewCounter;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/30 0030
 * Time: 下午 15:04
 */
use Illuminate\Support\ServiceProvider;
use Jacobcyl\ViewCounter\Commands\SetViewCounter;
use Jacobcyl\ViewCounter\Commands\SyncCounter;

class ViewCounterServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('counter', function(){
           return $this->app->make('Jacobcyl\ViewCounter\ViewCounter');
        });

        $this->mergeConfig();

        $this->registerCommands();
    }

    public function boot(){
        // Publish migrations files
        $this->publishes([
            __DIR__ . '/migrations/' => base_path('/database/migrations')
        ], 'migrations');

        // Publish config files
        $this->publishes([
            __DIR__.'/config.php' => config_path('counter.php'),
        ]);

        // Register commands
        $this->commands('command.counter.sync');
        $this->commands('command.counter.view');
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.counter.sync', function ($app) {
            return new SyncCounter();
        });

        $this->app->singleton('command.counter.view', function ($app) {
            return new SetViewCounter();
        });
    }

    /**
     * Merges user's and counter's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'counter'
        );
    }



    public function provides()
    {
        return ['viewCounter'];
    }
}