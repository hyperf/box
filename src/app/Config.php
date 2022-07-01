<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App;

use Hyperf\Utils\Arr;

class Config
{
    protected string $configFile;
    protected string $configFilePath;

    public function __construct()
    {
        $this->configFilePath = getenv('HOME') . '/.box';
        $this->configFile = $this->configFilePath . '/.boxconfig';
        $this->init();
    }

    public function getConfigContent(): array
    {
        return json_decode(file_get_contents($this->configFile), true);
    }

    public function setConfigContent(array $content): void
    {
        file_put_contents($this->configFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function updateConfig(string $key, ?string $value): void
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

    public function init(): void
    {
        if (! file_exists($this->configFile)) {
            if (! file_exists($this->configFilePath)) {
                mkdir($this->configFilePath, 0755, true);
            }
            file_put_contents($this->configFile, '{}');
            chmod($this->configFile, 0755);
        }
        $content = $this->getConfigContent();
        if (! $content) {
            $content = [
                'path' => [
                    'runtime' => getenv('HOME') . '/.box',
                    'bin' => getenv('HOME') . '/.box',
                ],
                'github' => [
                    'access-token' => '',
                ],
                'versions' => [
                    'php' => '8.1',
                ],
            ];
            $this->setConfigContent($content);
        }
    }
}
