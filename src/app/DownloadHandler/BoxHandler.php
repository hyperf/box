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

namespace App\DownloadHandler;

use Phar;
use App\Box;
use SplFileInfo;
use RuntimeException;

class BoxHandler extends AbstractDownloadHandler
{
    protected string $fullRepo = 'hyperf/box';

    protected string $binName = 'box';

    public function __construct()
    {
        parent::__construct();
        $this->binName = $this->getAssetName();
    }

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        if (version_compare(Box::VERSION, $this->versions($this->binName)[0] ?? '', '>=')) {
            throw new RuntimeException(sprintf("The latest version [v%s] is already installed.", Box::VERSION), 1);
        }

        $url = $this->fetchDownloadUrlFromGithubRelease($this->getAssetName(), $this->fullRepo, $version);
        $savePath = Phar::running(false) ?: $this->runtimePath . '/';

        return $this->download($url, $savePath, 0755, $this->binName);
    }

    public function versions(string $pkgName, array $options = []): array
    {
        return $this->fetchVersionsFromGithubRelease($this->fullRepo, $this->getAssetName());
    }

    protected function getAssetName(): string
    {
        return match (PHP_OS) {
            'Darwin' => 'box_x86_64_macos',
            'Linux' => match (php_uname('m')) {
                'x86_64' => 'box_x86_64_linux',
                default => 'box_aarch64_linux',
            }
        };
    }
}
