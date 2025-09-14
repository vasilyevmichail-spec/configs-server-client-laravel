<?php

namespace VM\ConfigManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;

class ConfigManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConfigManager::class, function (Application $app) {
            return new ConfigManager($app->basePath());
        });

        $this->app->alias(ConfigManager::class, 'config-manager');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishCommands();
        }
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/configmanager.php' => config_path('configmanager.php'),
        ], 'configmanager-config');
    }

    protected function publishCommands(): void
    {
        $this->commands([
            // Можно добавить кастомные команды в будущем
        ]);
    }
}