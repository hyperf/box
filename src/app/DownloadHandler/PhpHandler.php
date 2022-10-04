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

use App\PkgDefinition\Definition;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

class PhpHandler extends AbstractDownloadHandler
{
    #[Inject]
    protected Client $httpClient;

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $version = $this->prehandleVersion($version);
        try {
            $response = $this->getArtifact($this->getDefinition($pkgName), $version, 'cli');
            if ($response->getStatusCode() !== 302 || ! $response->getHeaderLine('Location')) {
                throw new \RuntimeException('Download failed, cannot retrieve the download url from artifact.');
            }
            $savePath = $this->runtimePath . '/php' . $version . '.zip';
            $this->download($response->getHeaderLine('Location'), $savePath, 0755);
            if (! file_exists($savePath)) {
                throw new \RuntimeException('Download failed, cannot locate the PHP bin file in local.');
            }
            if (! $this->isBinExists('unzip')) {
                throw new \RuntimeException('Download failed, unzip command not found.');
            }
            // Unzip the artifact file
            exec('unzip -o ' . $savePath . ' -d ' . $this->runtimePath);
            $this->logger->info('Unpacked zip file ' . $savePath);
            // ZipArchive::extractTo('runtime', $savePath);
            rename($renameFrom = $this->runtimePath . '/php', $renameTo = $this->runtimePath . '/php' . $version);
            $this->logger->info(sprintf('Renamed %s to %s', $renameFrom, $renameTo));
            unlink($savePath);
            if (file_exists($this->runtimePath . '/php.dwarf')) {
                unlink($this->runtimePath . '/php.dwarf');
            }
            if (file_exists($this->runtimePath . '/php.debug')) {
                unlink($this->runtimePath . '/php.debug');
            }
            $this->logger->info(sprintf('Deleted %s', $savePath));
            return new SplFileInfo($renameTo);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());
        }
        return null;
    }

    protected function matchArtifact(array $artifacts, string $search): array
    {
        foreach ($artifacts as $artifact) {
            if (str_contains($artifact['name'], $search)) {
                return $artifact;
            }
        }
        return [];
    }

    protected function prehandleVersion(string $version): string
    {
        if ($version === 'latest') {
            $version = '8.1';
        }
        return $version;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getArtifact(Definition $definition, string $version, string $prefix): ResponseInterface
    {
        $githubToken = $this->githubClient->getGithubToken();
        if (! $githubToken) {
            throw new \RuntimeException('Missing github access token, run `box config set github.access-token <Your Token>` to complete the configuration.');
        }
        $os = PHP_OS_FAMILY;
        $arch = php_uname('m');
        $key = $os . '.' . $arch;
        $response = $this->githubClient->getActionsArtifacts($definition->getRepo(), $definition->getJobs()?->getJob($key)?->getJobId());
        $searchKey = $this->buildSearchKey($definition, $key, $prefix, $version, $arch);
        $artifact = $this->matchArtifact($response['artifacts'] ?? [], $searchKey);
        if (! isset($artifact['archive_download_url'])) {
            throw new \RuntimeException('Does not match any artifact.');
        }
        return $this->httpClient->get($artifact['archive_download_url'], [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . $githubToken,
            ],
            'allow_redirects' => false,
        ]);
    }

    protected function buildSearchKey(Definition $definition, string $key, string $prefix, string $version, string $arch): string
    {
        return $this->replaces($definition->getJobArtifactMatchRule()[$key], [
            'prefix' => $prefix,
            'php-version' => $version,
            'arch' => $arch,
        ]);
    }

    protected function isBinExists(string $string): bool
    {
        $result = shell_exec(sprintf("which %s", escapeshellarg($string)));
        return ! empty($result) && ! str_contains($result, 'not found');
    }

    public function versions(string $pkgName, array $options = []): array
    {
        return $this->getDefinition($pkgName)->getVersions();
    }
}
