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

use App\Box;
use Phar;
use SplFileInfo;

class BoxHandler extends AbstractDownloadHandler
{

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $definition = $this->getDefinition($pkgName);
        $this->latestVersionCheck(Box::VERSION, $definition, $options);
        $assetName = $definition->getReleaseAssetMatchRule()[PHP_OS_FAMILY . '.' . php_uname('m')] ?? '';
        if (! $assetName) {
            throw new BoxException('Can not found any matched asset for your system.');
        }
        $url = $this->fetchDownloadUrlFromGithubRelease($assetName, $definition->getRepo(), $version);
        $savePath = Phar::running(false) ?: $this->runtimePath . '/';

        return $this->download($url, $savePath, 0755, $definition->getBin());
    }

    public function versions(string $pkgName, array $options = []): array
    {
        $definition = $this->getDefinition($pkgName);
        return $this->fetchVersionsFromGithubRelease($definition->getRepo(), $definition->getReleaseAssetKeyword());
    }
}
