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

use App\Exception\NotSupportVersionsException;
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
            if ($version === 'latest' && $definition->getLatest()) {
                $version = $definition->getLatest();
            }
            $url = str_replace('${{version}}', $version, $definition->getUrl());
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
        if (! $definition->getRepo()) {
            throw new NotSupportVersionsException($pkgName);
        }
        return $this->fetchVersionsFromGithubRelease($definition->getRepo(), $definition->getBin());
    }

}
