<?php

namespace VM\ConfigManager;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class ConfigManager
{
    /**
     * Загружает конфигурацию из config.php и .env файлов
     * Конфигурация из .env имеет приоритет
     *
     * @return void
     */
    public function loadConfiguration(): void
    {
        $this->loadConfigFile();
        $this->overrideWithEnv();
    }

    /**
     * Загружает конфигурацию из config.php файла
     *
     * @return void
     */
    protected function loadConfigFile(): void
    {
        $configPath = config_path('config.php');

        if (!File::exists($configPath)) {
            return;
        }

        $config = require $configPath;

        if (function_exists('get_config') && is_callable('get_config')) {
            $config = array_merge($config, get_config());
        }

        foreach ($config as $key => $value) {
            Config::set($key, $value);
        }
    }

    /**
     * Переопределяет значения из config.php значениями из .env
     *
     * @return void
     */
    protected function overrideWithEnv(): void
    {
        // Получаем все переменные окружения, которые начинаются с префиксов Laravel
        $envVariables = $_ENV;

        foreach ($envVariables as $key => $value) {
            // Игнорируем системные переменные и переменные без префиксов
            if ($this->isLaravelConfigKey($key)) {
                $configKey = $this->convertEnvKeyToConfigKey($key);
                Config::set($configKey, $value);
            }
        }
    }

    /**
     * Проверяет, является ли ключ конфигурационным ключом Laravel
     *
     * @param string $key
     * @return bool
     */
    protected function isLaravelConfigKey(string $key): bool
    {
        $laravelPrefixes = [
            'APP_', 'DB_', 'MAIL_', 'REDIS_', 'CACHE_', 'SESSION_',
            'QUEUE_', 'BROADCAST_', 'LOG_', 'FILESYSTEM_', 'VIEW_',
            'BROADCASTING_', 'SERVICES_', 'AUTH_'
        ];

        foreach ($laravelPrefixes as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Конвертирует ключ из .env формата в формат конфигурации Laravel
     *
     * @param string $envKey
     * @return string
     */
    protected function convertEnvKeyToConfigKey(string $envKey): string
    {
        $mapping = [
            'APP_' => 'app.',
            'DB_' => 'database.connections.mysql.',
            'MAIL_' => 'mail.',
            'REDIS_' => 'database.redis.',
            'CACHE_' => 'cache.',
            'SESSION_' => 'session.',
            'QUEUE_' => 'queue.',
            'BROADCAST_' => 'broadcasting.',
            'LOG_' => 'logging.',
            'FILESYSTEM_' => 'filesystems.',
            'VIEW_' => 'view.',
        ];

        foreach ($mapping as $envPrefix => $configPrefix) {
            if (str_starts_with($envKey, $envPrefix)) {
                $keyWithoutPrefix = strtolower(substr($envKey, strlen($envPrefix)));
                $keyWithDots = str_replace('_', '.', $keyWithoutPrefix);
                return $configPrefix . $keyWithDots;
            }
        }

        // Для кастомных ключей, не входящих в стандартные префиксы
        return strtolower(str_replace('_', '.', $envKey));
    }

    /**
     * Получает значение конфигурации с учетом приоритета .env
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Сначала проверяем .env
        $envKey = $this->convertConfigKeyToEnvKey($key);
        if (isset($_ENV[$envKey])) {
            return $_ENV[$envKey];
        }

        // Затем проверяем config.php
        return Config::get($key, $default);
    }

    /**
     * Конвертирует ключ конфигурации в формат .env
     *
     * @param string $configKey
     * @return string
     */
    protected function convertConfigKeyToEnvKey(string $configKey): string
    {
        $mapping = [
            'app.' => 'APP_',
            'database.connections.mysql.' => 'DB_',
            'mail.' => 'MAIL_',
            'database.redis.' => 'REDIS_',
            'cache.' => 'CACHE_',
            'session.' => 'SESSION_',
            'queue.' => 'QUEUE_',
            'broadcasting.' => 'BROADCAST_',
            'logging.' => 'LOG_',
            'filesystems.' => 'FILESYSTEM_',
            'view.' => 'VIEW_',
        ];

        foreach ($mapping as $configPrefix => $envPrefix) {
            if (str_starts_with($configKey, $configPrefix)) {
                $keyWithoutPrefix = substr($configKey, strlen($configPrefix));
                $keyWithUnderscores = strtoupper(str_replace('.', '_', $keyWithoutPrefix));
                return $envPrefix . $keyWithUnderscores;
            }
        }

        // Для кастомных ключей
        return strtoupper(str_replace('.', '_', $configKey));
    }
}