<?php namespace Fedeisas\LaravelMailCssInliner;

use Illuminate\Support\ServiceProvider;
use Swift_Mailer;

class LaravelMailCssInlinerServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/css-inliner.php' => config_path('css-inliner.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/css-inliner.php', 'css-inliner');

        $this->app->singleton('Fedeisas\LaravelMailCssInliner\CssInlinerPlugin', function ($app) {
            $options = $app['config']->get('css-inliner');
            return new CssInlinerPlugin($options);
        });

        $this->app->extend('swift.mailer', function (Swift_Mailer $swiftMailer, $app) {
            $inlinerPlugin = $app->make('Fedeisas\LaravelMailCssInliner\CssInlinerPlugin');
            $swiftMailer->registerPlugin($inlinerPlugin);
            return $swiftMailer;
        });
    }
}
