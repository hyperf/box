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

class DefaultHandler extends AbstractDownloadHandler
{
    protected array $definitions = [
        'php-cs-fixer' => [
            'repo' => 'FriendsOfPHP/PHP-CS-Fixer',
            'bin' => 'php-cs-fixer.phar',
        ],
    ];

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        if (! isset($this->definitions[$repo])) {
            throw new \RuntimeException('The package not found');
        }
        $definition = $this->definitions[$repo];
        $url = $this->fetchDownloadUrlFromGithubRelease($definition['bin'], $definition['repo'], $version);
        return $this->download($url, $this->runtimePath . '/', 0755);
    }
}
