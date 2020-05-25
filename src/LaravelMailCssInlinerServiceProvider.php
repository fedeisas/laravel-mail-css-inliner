<?php namespace Fedeisas\LaravelMailCssInliner;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class LaravelMailCssInlinerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
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
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/css-inliner.php', 'css-inliner');

        $this->app->singleton(CssInlinerPlugin::class, function ($app) {
            return new CssInlinerPlugin($app['config']->get('css-inliner'));
        });

        $this->app->afterResolving('mail.manager', function (MailManager $mailManager) {
            $mailManager->getSwiftMailer()->registerPlugin($this->app->make(CssInlinerPlugin::class));
            return $mailManager;
        });
    }
}
