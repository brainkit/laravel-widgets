<?php

namespace Brainkit\Widgets;

use Brainkit\Widgets\Console\WidgetMakeCommand;
use Brainkit\Widgets\Factories\AsyncWidgetFactory;
use Brainkit\Widgets\Factories\WidgetFactory;
use Brainkit\Widgets\Misc\LaravelApplicationWrapper;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    use AppNamespaceDetectorTrait;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'laravel-widgets'
        );

        $config = [
            'defaultNamespace' => config('laravel-widgets.default_namespace') ?: $this->getAppNamespace().'Widgets',
        ];

        $this->app->bind('brainkit.widget', function () use ($config) {
            return new WidgetFactory($config, new LaravelApplicationWrapper());
        });

        $this->app->bind('brainkit.async-widget', function () use ($config) {
            return new AsyncWidgetFactory($config, new LaravelApplicationWrapper());
        });

        $this->app->singleton('command.widget.make', function ($app) {
            return new WidgetMakeCommand($app['files']);
        });

        $this->commands('command.widget.make');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('laravel-widgets.php'),
        ]);

        $routeConfig = [
            'namespace' => 'Brainkit\Widgets\Controllers',
            'prefix'    => 'brainkit',
        ];

        $this->app['router']->group($routeConfig, function ($router) {
            $router->post('load-widget', 'WidgetController@showWidget');
        });

        $this->registerBladeExtensions();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['brainkit.widget', 'brainkit.async-widget'];
    }

    /**
     * Register blade extensions.
     */
    protected function registerBladeExtensions()
    {
        Blade::extend(function ($view) {
            $pattern = $this->createMatcher('widget');

            return preg_replace($pattern, '$1<?php echo app("brainkit.widget")->run$2; ?>', $view);
        });

        Blade::extend(function ($view) {
            $pattern = $this->createMatcher('async-widget');

            return preg_replace($pattern, '$1<?php app("brainkit.async-widget")->run$2; ?>', $view);
        });

        Blade::extend(function ($view) {
            $pattern = $this->createMatcher('asyncWidget');

            return preg_replace($pattern, '$1<?php echo app("brainkit.async-widget")->run$2; ?>', $view);
        });

        Blade::extend(function ($view) {
            $pattern = $this->createMatcher('widgetGroup');

            return preg_replace($pattern, '$1<?php echo Widget::group$2->display(); ?>', $view);
        });
    }

    /**
     * Substitution for $compiler->createMatcher().
     *
     * Get the regular expression for a generic Blade function.
     *
     * @param string $function
     *
     * @return string
     */
    protected function createMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'(\s*\(.*\))/';
    }
}
