<?php

namespace VM\ConfigManager;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ConfigManager::class, function ($app) {
            return new ConfigManager();
        });

        $this->app->alias(ConfigManager::class, 'config-manager');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->app->make(ConfigManager::class);
        $configManager->loadConfiguration();
    }
}