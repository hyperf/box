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
use App\PkgDefinition\Definition;
use SplFileInfo;

class ComposerHandler extends AbstractDownloadHandler
{


    protected string $fullRepo = 'composer/composer';

    protected string $githubBaseUrl = 'github.com';

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $definition = $this->getDefinition($pkgName);
        if (! isset($options['source'])) {
            $options['source'] = $definition->getSources()->getSource('default')?->getUrl();
        }
        $url = match ($options['source']) {
            $this->githubBaseUrl => $this->fetchDownloadUrlFromGithubRelease($definition->getBin(), $definition->getRepo(), $version),
            default => $this->fetchDownloadUrlFromGetComposerOrg($definition, $version),
        };
        return $this->download($url, $this->runtimePath . '/', 0755);
    }

    public function versions(string $pkgName, array $options = []): array
    {
        $definition = $this->getDefinition($pkgName);
        if (! isset($options['source'])) {
            $options['source'] = $definition->getSources()->getSource('default')?->getUrl();
        }
        return match ($options['source']) {
            $this->githubBaseUrl => $this->fetchVersionsFromGithubRelease($definition->getRepo()),
            default => throw new NotSupportVersionsException($pkgName),
        };
    }

    protected function fetchDownloadUrlFromGetComposerOrg(Definition $definition, string $version): string
    {
        if ($version === 'latest') {
            $release = $this->githubClient->getRelease($definition->getRepo(), $version);
            if (! isset($release['tag_name'])) {
                throw new \RuntimeException('Cannot match the specified version from github releases.');
            }
            $specifiedVersion = $release['tag_name'];
        } else {
            $specifiedVersion = $version;
        }
        $url = $definition->getSources()?->getSource('getcomposer.org')?->getUrl();
        if (! $url) {
            throw new \RuntimeException('Cannot parse the download url by getcomposer.org.');
        }
        return $this->replaces($url, [
            'version' => $specifiedVersion,
            'bin' => $definition->getBin(),
        ]);
    }

}
