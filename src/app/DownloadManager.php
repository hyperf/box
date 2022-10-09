<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App;

use App\DownloadHandler\AbstractDownloadHandler;
use App\DownloadHandler\BoxHandler;
use App\DownloadHandler\ComposerHandler;
use App\DownloadHandler\DefaultHandler;
use App\DownloadHandler\MicroHandler;
use App\DownloadHandler\PhpHandler;
use App\DownloadHandler\PintHandler;
use App\DownloadHandler\SwooleCliHandler;
use App\Exception\PkgDefinitionNotFoundException;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use SplFileInfo;

class DownloadManager
{
    protected array $handlers = [
        'box' => BoxHandler::class,
        'composer' => ComposerHandler::class,
        'micro' => MicroHandler::class,
        'php' => PhpHandler::class,
        'pint' => PintHandler::class,
        'swoole-cli' => SwooleCliHandler::class,
        'default' => DefaultHandler::class,
    ];

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected Config $config;

    #[Inject]
    protected PkgDefinitionManager $pkgDefinitionManager;

    public function get(string $pkg, string $version, array $options = []): void
    {
        $handler = $this->buildHandler($pkg);
        $this->createRuntimePath();
        $file = $handler->handle($pkg, $version, $options);
        if ($file instanceof SplFileInfo && $file->isWritable()) {
            chmod($file->getRealPath(), 0755);
        }
    }

    public function versions(string $pkg, array $options): array
    {
        $handler = $this->buildHandler($pkg);
        return $handler->versions($pkg, $options);
    }

    protected function createRuntimePath(): void
    {
        $base = getenv('HOME') ?: getenv('USERPROFILE');
        $path = $this->config->getConfig('path.runtime', $base . DIRECTORY_SEPARATOR . '.box');
        if (! file_exists($path)) {
            mkdir($path, 0755);
            chmod($path, 0755);
        }
    }

    protected function buildHandler(string &$pkg): AbstractDownloadHandler
    {
        if (! $this->pkgDefinitionManager->hasDefinition($pkg)) {
            throw new PkgDefinitionNotFoundException($pkg);
        }
        $key = 'default';
        if (isset($this->handlers[$pkg])) {
            $key = $pkg;
        }
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        if ($key === 'php' && $kernel === 'swoole') {
            $key = $pkg = 'swoole-cli';
        }
        return $this->container->get($this->handlers[$key]);
    }
}
