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
use Phar;
use SplFileInfo;

class PintHandler extends AbstractDownloadHandler
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

        $savePath = Phar::running(false) ?: $this->runtimePath . '/';
        $file = $this->download($url, $savePath, 0755);
        if (! file_exists($savePath)) {
            throw new \RuntimeException('Download failed, cannot locate the file in local.');
        }

        // Is file name ends with .zip?
        if (str_ends_with($file->getFilename(), '.zip')) {
            $zip = new \ZipArchive();
            $zip->open($file->getRealPath());
            $this->logger->info('Unpacking zip file ' . $savePath);
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $filename = $zip->getNameIndex($i);
                if (str_ends_with($filename, 'builds/pint')) {
                    copy('zip://' . $file->getRealPath() . '#' . $filename, $savePath . $definition->getBin());
                }
            }
            $zip->close();
            $this->logger->info('Unpacked zip file ' . $savePath);
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
        if (! $definition) {
            throw new \RuntimeException('The package not found');
        }
        if (! $definition->getRepo() && ! $definition->getComposerName()) {
            throw new NotSupportVersionsException($pkgName);
        }
        if ($definition->getLatestFetchType() === 'github') {
            return $this->fetchVersionsFromGithubRelease($definition->getRepo(), $definition->getBin());
        }
        if ($definition->getLatestFetchType() === 'packagist') {
            return $this->fetchVersionsFromPackagist($definition->getPkgName(), $definition->getComposerName());
        }
        throw new BoxException('The definition of package is invalid');
    }
}
