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

use App\Exception\PackageNotFoundException;
use Phar;
use SplFileInfo;

class SwooleCliHandler extends AbstractDownloadHandler
{

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $definition = $this->getDefinition($pkgName);
        if ($version === 'latest') {
            if ($definition->getLatest() !== null && $definition->getLatest() !== 'latest') {
                $specifiedVersion = $definition->getLatest();
            } else {
                $release = $this->githubClient->getRelease($definition->getRepo(), $version);
                if (! isset($release['tag_name'])) {
                    throw new \RuntimeException('Cannot match the specified version from github releases.');
                }
                $specifiedVersion = $release['tag_name'];
            }
        } else {
            $specifiedVersion = $version;
        }
        $matchRules = $definition->getReleaseAssetMatchRule();
        $matchRule = null;
        $os = PHP_OS;
        if (isset($matchRules[$os])) {
            $matchRule = $matchRules[$os];
        }
        if (! $matchRule) {
            throw new PackageNotFoundException($pkgName);
        } else {
            $assetName = $this->replaces($matchRule, ['version' => $specifiedVersion]);
        }
        $url = $this->fetchDownloadUrlFromGithubRelease($assetName, $definition->getRepo(), $specifiedVersion);
        $savePath = Phar::running(false) ?: $this->runtimePath . DIRECTORY_SEPARATOR;

        $file = $this->download($url, $savePath, 0755);
        if (! file_exists($savePath)) {
            throw new \RuntimeException('Download failed, cannot locate the file in local.');
        }
        // Is file name ends with .zip?
        if (str_ends_with($file->getFilename(), '.zip')) {
            $zip = new \ZipArchive();
            $zip->open($file->getRealPath());
            $this->logger->info('Unpacking zip file ' . $savePath);
            $zip->extractTo($savePath);
            $zip->close();
            $this->logger->info('Unpacked zip file ' . $savePath);
            unlink($file->getRealPath());
        }
        // Is file name ends with .tar.xz?
        if (str_ends_with($file->getFilename(), '.tar.xz')) {
            $this->logger->info('Unpacking tar.xz file ' . $savePath);
            exec(sprintf('tar -xvf %s -C %s', $file->getRealPath(), $savePath));
            $this->logger->info('Unpacked tar.xz file ' . $savePath);
            unlink($file->getRealPath());
        }
        // Is file name ends with .tar.gz?
        if (str_ends_with($file->getFilename(), '.tar.gz')) {
            $this->logger->info('Unpacking tar.gz file ' . $savePath);
            exec(sprintf('tar -xvf %s -C %s', $file->getRealPath(), $savePath));
            $this->logger->info('Unpacked tar.gz file ' . $savePath);
            unlink($file->getRealPath());
        }
        $licenseFile = $savePath . 'LICENSE';
        // If license file exists, delete it.
        if (file_exists($licenseFile)) {
            unlink($licenseFile);
        }
        return new SplFileInfo($savePath . $definition->getBin());
    }

    public function versions(string $pkgName, array $options = []): array
    {
        $definition = $this->getDefinition($pkgName);
        return $this->fetchVersionsFromGithubRelease($definition->getRepo(), $definition->getReleaseAssetKeyword());
    }

}
