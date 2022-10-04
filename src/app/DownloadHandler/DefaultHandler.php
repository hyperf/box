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

use App\Exception\BoxException;
use App\Exception\NotSupportVersionsException;
use App\PkgDefinition\Definition;
use GuzzleHttp\Client;
use SplFileInfo;

class DefaultHandler extends AbstractDownloadHandler
{
    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $definition = $this->getDefinition($pkgName);
        if (! $definition) {
            throw new \RuntimeException('The package not found');
        }
        if ($definition->getRepo()) {
            $url = $this->fetchDownloadUrlFromGithubRelease($definition->getBin(), $definition->getRepo(), $version);
        } elseif ($definition->getUrl()) {
            if ($version === 'latest') {
                if ($definition->getLatest() && $definition->getLatest() !== 'latest') {
                    $specifiedVersion = $definition->getLatest();
                } else {
                    $versions = $this->versions($pkgName);
                    $specifiedVersion = array_shift($versions);
                }
            } else {
                $specifiedVersion = $version;
            }
            $url = str_replace('${{version}}', $specifiedVersion, $definition->getUrl());
        } else {
            throw new \RuntimeException('The definition of package is invalid');
        }
        return $this->download($url, $this->runtimePath . '/', 0755, $definition->getBin());
    }

    public function versions(string $pkgName, array $options = []): array
    {
        $definition = $this->getDefinition($pkgName);
        if (! $definition) {
            throw new \RuntimeException('The package not found');
        }
        if (! $definition->getRepo() && ! $definition->getComposerName()) {
            throw new NotSupportVersionsException($pkgName);
        }
        if ($definition->getLatestFetchType() === 'github') {
            return $this->fetchVersionsFromGithubRelease($definition->getRepo(), $definition->getBin());
        } elseif ($definition->getLatestFetchType() === 'packagist') {
            return $this->fetchVersionsFromPackagist($definition->getPkgName(), $definition->getComposerName());
        } else {
            throw new BoxException('The definition of package is invalid');
        }
    }
}
