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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

class PhpHandler extends AbstractDownloadHandler
{
    #[Inject]
    protected Client $httpClient;

    protected string $repo = 'dixyes/lwmbs';

    protected array $jobs
        = [
            'Darwin.x86_64' => '2554976476',
            'Darwin.arm64' => '2554976476',
        ];

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        $version = $this->prehandleVersion($version);
        try {
            $response = $this->getArtifact($version, 'cli');
            if ($response->getStatusCode() !== 302 || ! $response->getHeaderLine('Location')) {
                throw new \RuntimeException('Download failed, cannot retrieve the download url from artifact.');
            }
            $savePath = $this->runtimePath . '/php' . $version . '.zip';
            $this->download($response->getHeaderLine('Location'), $savePath, 0755);
            if (! file_exists($savePath)) {
                throw new \RuntimeException('Download failed, cannot locate the PHP bin file in local.');
            }
            // Unzip the artifact file
            exec('unzip ' . $savePath . ' -d ' . $this->runtimePath);
            $this->logger->info('Unpacked zip file ' . $savePath);
            // ZipArchive::extractTo('runtime', $savePath);
            rename($renameFrom = $this->runtimePath . '/php', $renameTo = $this->runtimePath . '/php' . $version);
            $this->logger->info(sprintf('Renamed %s to %s', $renameFrom, $renameTo));
            unlink($savePath);
            unlink($this->runtimePath . '/php.dwarf');
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
    protected function getArtifact(string $version, string $prefix): ResponseInterface
    {
        $githubToken = $this->githubClient->getGithubToken();
        if (! $githubToken) {
            throw new \RuntimeException('Missing github access token, run `box config set github.access-token <Your Token>` to complete the configuration.');
        }
        $os = PHP_OS_FAMILY;
        $arch = php_uname('m');
        $key = $os . '.' . $arch;
        $response = $this->githubClient->getActionsArtifacts($this->repo, $this->jobs[$key]);
        $searchKey = implode('_', [$prefix, $version, $arch]);
        $artifact = $this->matchArtifact($response['artifacts'] ?? [], $searchKey);
        return $this->httpClient->get($artifact['archive_download_url'], [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . $githubToken,
            ],
            'allow_redirects' => false,
        ]);
    }
}
