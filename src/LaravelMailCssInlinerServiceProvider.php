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
        $this->mergeConfigFrom(__DIR__ . '/../config/css-inliner.php', 'secure');

        $this->app->singleton(CssInlinerPlugin::class, function ($app) {
            $config = $app['config'];
            return new CssInlinerPlugin(
                $config->get('css-inliner.css-files', []),
                $config->get('css-inliner.secure')
            );
        });

        Event::listen(MessageSending::class, CssInlinerPlugin::class);
    }
}
