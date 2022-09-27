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
use SplFileInfo;

class BoxHandler extends AbstractDownloadHandler
{
    protected string $fullRepo = 'hyperf/box';

    protected string $binName = 'box';

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        $assetName = match (PHP_OS) {
            'Darwin' => 'box_x86_64_macos',
            'Linux' => match (php_uname('m')) {
                'x86_64' => 'box_x86_64_linux',
                default => 'box_aarch64_linux',
            }
        };
        $url = $this->fetchDownloadUrlFromGithubRelease($assetName, $this->fullRepo, $version);
        $savePath = Phar::running(false) ?: $this->runtimePath . '/';

        return $this->download($url, $savePath, 0755, $this->binName);
    }
}
