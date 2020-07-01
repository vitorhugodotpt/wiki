<?php

namespace Pdmfc\Wiki;

use Pdmfc\Wiki\Commands\AssetCommand;
use Pdmfc\Wiki\Commands\GenerateDocumentationCommand;
use Pdmfc\Wiki\Commands\InstallCommand;
use Pdmfc\Wiki\Commands\ThemeCommand;
use Pdmfc\Wiki\Facades\LaRecipe as LaRecipeFacade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Pdmfc\Wiki\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wiki');

        Route::group($this->routesConfig(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/LaRecipe.php');
        });
    }

    /**
     * @return array
     */
    protected function routesConfig()
    {
        return [
            'prefix'     => config('wiki.docs.route'),
            'namespace'  => 'Pdmfc\Wiki\Http\Controllers',
            //'domain'     => config('wiki.domain', null),
            'as'         => 'wiki.',
            'middleware' => config('wiki.docs.middleware'),
        ];
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfigs();
        $this->loadHelpers();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        $this->app->alias('LaRecipe', LaRecipeFacade::class);

        $this->app->singleton('LaRecipe', function () {
            return new LaRecipe();
        });
    }

    /**
     * Register the publishable files.
     */
    protected function registerPublishableResources()
    {
        $publishablePath = dirname(__DIR__) . '/publishable';

        $publishable = [
            'larecipe_config' => [
                "{$publishablePath}/config/larecipe.php" => config_path('larecipe.php'),
            ],
            'larecipe_assets' => [
                "{$publishablePath}/assets/" => public_path('vendor/binarytorch/larecipe/assets'),
            ],
            'larecipe_views' => [
                dirname(__DIR__) . "/resources/views/partials" => resource_path('views/vendor/larecipe/partials'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

    /**
     * Load helpers.
     */
    protected function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Register the commands accessible from the Console.
     */
    protected function registerConsoleCommands()
    {
        $this->commands(AssetCommand::class);
        $this->commands(ThemeCommand::class);
        $this->commands(InstallCommand::class);
        $this->commands(GenerateDocumentationCommand::class);
    }

    /**
     * Register the package configs.
     */
    protected function registerConfigs()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/publishable/config/larecipe.php',
            'larecipe'
        );
    }
}
