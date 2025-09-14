<?php

namespace VM\ConfigManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array all()
 * @method static bool has(string $key)
 * @method static void set(string $key, mixed $value)
 * @method static void load()
 * @method static string|null getFilePath()
 * @method static bool isLoaded()
 *
 * @see \VM\ConfigManager\ConfigManager
 */
class ConfigManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VM\ConfigManager\ConfigManager::class;
    }
}