<?php namespace Fedeisas\LaravelMailCssInliner;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LaravelMailCssInlinerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/css-inliner.php' => base_path('config/css-inliner.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/css-inliner.php', 'css-files');

        $this->app->singleton(CssInlinerPlugin::class, function ($app) {
            return new CssInlinerPlugin($app['config']->get('css-inliner.css-files', []));
        });

        Event::listen(MessageSending::class, CssInlinerPlugin::class);
    }
}
