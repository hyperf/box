<?php

namespace App;


use Hyperf\Utils\Arr;

class Config
{

    protected string $configFile;

    public function __construct()
    {
        $this->configFile = getenv('HOME') . '/.box/.boxconfig';
    }


    public function getConfigContent(): array
    {
        if (! file_exists($this->configFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->configFile), true);
    }

    public function setConfigContent(array $content): void
    {
        if (! file_exists($this->configFile)) {
            file_put_contents($this->configFile, '{}');
            chmod($this->configFile, 0755);
        }
        file_put_contents($this->configFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function updateConfig(string $key, string $value): void
    {
        $config = $this->getConfigContent();
        if (in_array($value, ['bool', 'false'])) {
            $value = boolval($value);
        }
        Arr::set($config, $key, $value);
        $this->setConfigContent($config);
    }

    public function getConfig(?string $key, mixed $default = null): mixed
    {
        $config = $this->getConfigContent();
        return Arr::get($config, $key, $default);
    }

}