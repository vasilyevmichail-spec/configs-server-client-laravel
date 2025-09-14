<?php

namespace VM\ConfigManager;

class EarlyLoader
{
    public static function load(string $basePath): void
    {
        $configFile = self::detectConfigFile($basePath);

        if (!$configFile || !file_exists($configFile)) {
            return;
        }

        $variables = self::parseFile($configFile);

        foreach ($variables as $key => $value) {
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    protected static function detectConfigFile(string $basePath): ?string
    {
        $possibleFiles = [
            $basePath . '.my_env',
            $basePath . 'custom.env',
            $basePath . 'config.env'
        ];

        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }

    public static function parseFile(string $filePath): array
    {
        $variables = [];
        $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines) {
            return $variables;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Пропускаем комментарии и пустые строки
            if (str_starts_with($line, '#') || str_starts_with($line, '//') || $line === '') {
                continue;
            }

            // Разбираем строку на ключ и значение
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Обрабатываем кавычки
                $value = self::parseValue($value);

                $variables[$key] = $value;
            }
        }

        return $variables;
    }

    protected static function parseValue(string $value): string
    {
        // Удаляем обрамляющие кавычки
        if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
            return $matches[2];
        }

        // Обрабатываем булевы значения
        if (strtolower($value) === 'true') return 'true';
        if (strtolower($value) === 'false') return 'false';
        if (strtolower($value) === 'null') return '';

        return $value;
    }
}