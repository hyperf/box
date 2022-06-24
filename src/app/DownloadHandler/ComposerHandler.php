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

class ComposerHandler extends AbstractDownloadHandler
{
    protected string $fullRepo = 'composer/composer';

    protected string $binName = 'composer.phar';

    protected string $getComposerOrgBaseUrl = 'getcomposer.org';

    protected string $githubBaseUrl = 'github.com';

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        if (! isset($options['source'])) {
            $options['source'] = $this->getComposerOrgBaseUrl;
        }
        $url = match ($options['source']) {
            $this->githubBaseUrl => $this->fetchDownloadUrlFromGithubRelease($this->binName, $this->fullRepo, $version),
            default => $this->fetchDownloadUrlFromGetComposerOrg($version),
        };
        return $this->download($url, $this->runtimePath . '/', 0755);
    }

    protected function fetchDownloadUrlFromGetComposerOrg(string $version): string
    {
        if ($version === 'latest') {
            $release = $this->githubClient->getRelease($this->fullRepo, $version);
            if (! isset($release['tag_name'])) {
                throw new \RuntimeException('Cannot match the specified version from github releases.');
            }
            $specifiedVersion = $release['tag_name'];
        } else {
            $specifiedVersion = $version;
        }
        return 'https://' . $this->getComposerOrgBaseUrl . '/download/' . $specifiedVersion . '/' . $this->binName;
    }
}
