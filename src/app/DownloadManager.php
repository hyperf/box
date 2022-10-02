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

use App\DownloadHandler\BoxHandler;
use App\DownloadHandler\ComposerHandler;
use App\DownloadHandler\DefaultHandler;
use App\DownloadHandler\MicroHandler;
use App\DownloadHandler\PhpHandler;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

class DownloadManager
{
    protected array $handlers = [
        'box' => BoxHandler::class,
        'composer' => ComposerHandler::class,
        'micro' => MicroHandler::class,
        'php' => PhpHandler::class,
        'default' => DefaultHandler::class,
    ];

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected Config $config;

    public function get(string $pkg, string $version, array $options = []): void
    {
        $this->createRuntimePath();
        /** @var \App\DownloadHandler\AbstractDownloadHandler $handler */
        $key = 'default';
        if (isset($this->handlers[$pkg])) {
            $key = $pkg;
        }
        $handler = $this->container->get($this->handlers[$key]);
        $file = $handler->handle($pkg, $version, $options);
        chmod($file->getRealPath(), 0755);
    }

    public function versions(string $pkg, array $options): array
    {
        /** @var \App\DownloadHandler\AbstractDownloadHandler $handler */
        $key = 'default';
        if (isset($this->handlers[$pkg])) {
            $key = $pkg;
        }
        $handler = $this->container->get($this->handlers[$key]);
        return $handler->versions($pkg, $options);
    }

    protected function createRuntimePath(): void
    {
        $path = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        if (! file_exists($path)) {
            mkdir($path, 0755);
            chmod($path, 0755);
        }
    }
}
