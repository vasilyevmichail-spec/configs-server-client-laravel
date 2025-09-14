<?php

namespace VM\ConfigManager;

use Illuminate\Support\Arr;

class ConfigManager
{
    protected string $filePath;
    protected array $variables = [];
    protected bool $loaded = false;

    public function __construct(string $basePath)
    {
        $this->filePath = $this->detectConfigFile($basePath);
    }

    public function load(): void
    {
        if ($this->loaded || !$this->filePath) {
            return;
        }

        $this->variables = EarlyLoader::parseFile($this->filePath);
        $this->loaded = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->load();
        return Arr::get($this->variables, $key, $default);
    }

    public function all(): array
    {
        $this->load();
        return $this->variables;
    }

    public function has(string $key): bool
    {
        $this->load();
        return Arr::has($this->variables, $key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->load();
        $this->variables[$key] = $value;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    protected function detectConfigFile(string $basePath): ?string
    {
        $possibleFiles = [
            $basePath . DIRECTORY_SEPARATOR . '.my_env',
            $basePath . DIRECTORY_SEPARATOR . 'custom.env',
            $basePath . DIRECTORY_SEPARATOR . 'config.env',
            $basePath . DIRECTORY_SEPARATOR . '.env.custom'
        ];

        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }
}