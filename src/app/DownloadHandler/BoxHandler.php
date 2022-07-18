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

use SplFileInfo;

class BoxHandler extends ComposerHandler
{
    protected string $fullRepo = 'hyperf/box';

    protected string $binName = 'box';

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        $this->binName = match(PHP_OS) {
            "Darwin" => 'box_php8.1_x86_64_macos',
            "Linux" => match(php_uname('m')) {
                'x86_64' => 'box_php8.1_x86_64_linux',
                default => 'box_php8.1_aarch64_linux',
            }
        };

        $url = $this->fetchDownloadUrlFromGithubRelease($this->binName, $this->fullRepo, $version);

        $this->download($url, $this->runtimePath . '/', 0755);

        rename($this->runtimePath . '/' .$this->binName, $renameTo = $this->runtimePath . '/box');

        return new SplFileInfo($renameTo);
    }
}
