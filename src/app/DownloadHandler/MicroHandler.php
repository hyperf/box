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
use SplFileInfo;

class MicroHandler extends PhpHandler
{
    #[Inject]
    protected Client $httpClient;

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
        $version = $this->prehandleVersion($version);
        try {
            $response = $this->getArtifact($version, 'micro');
            if ($response->getStatusCode() !== 302 || ! $response->getHeaderLine('Location')) {
                throw new \RuntimeException('Download failed, cannot retrieve the download url from artifact.');
            }
            $savePath = $this->runtimePath . '/micro_php' . $version . '.zip';
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
            rename($renameFrom = $this->runtimePath . '/micro.sfx', $renameTo = $this->runtimePath . '/micro_php' . $version . '.sfx');
            $this->logger->info(sprintf('Renamed %s to %s', $renameFrom, $renameTo));
            unlink($savePath);
            if (file_exists($this->runtimePath . '/micro.sfx.dwarf')) {
                unlink($this->runtimePath . '/micro.sfx.dwarf');
            }
            if (file_exists($this->runtimePath . '/micro.sfx.debug')) {
                unlink($this->runtimePath . '/micro.sfx.debug');
            }
            $this->logger->info(sprintf('Deleted %s', $savePath));
            return new SplFileInfo($renameTo);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());
        }
        return null;
    }
}
