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
use ZipArchive;

class MicroHandler extends PhpHandler
{
    #[Inject]
    protected Client $httpClient;

    public function handle(string $pkgName, string $version, array $options = []): ?SplFileInfo
    {
        $version = $this->prehandleVersion($version);
        try {
            $response = $this->getArtifact($this->getDefinition($pkgName), $version, 'micro');
            if ($response->getStatusCode() !== 302 || ! $response->getHeaderLine('Location')) {
                throw new \RuntimeException('Download failed, cannot retrieve the download url from artifact.');
            }
            $savePath = $this->runtimePath . DIRECTORY_SEPARATOR . 'micro_php' . $version . '.zip';
            $this->download($response->getHeaderLine('Location'), $savePath, 0755);
            if (! file_exists($savePath)) {
                throw new \RuntimeException('Download failed, cannot locate the PHP bin file in local.');
            }
            // Unzip the artifact file
            $this->logger->info('Unpacking zip file ' . $savePath);
            $renameTo = $this->runtimePath . DIRECTORY_SEPARATOR . 'micro_php' . $version . '.sfx';
            $zip = new ZipArchive();
            $zip->open($savePath);
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $filename = $zip->getNameIndex($i);
                if ($filename === 'micro.sfx') {
                    copy('zip://' . $savePath . '#' . $filename, $renameTo);
                }
            }
            $zip->close();
            $this->logger->info('Unpacked zip file ' . $savePath);
            unlink($savePath);
            $this->logger->info(sprintf('Deleted %s', $savePath));
            return new SplFileInfo($renameTo);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());
        }
        return null;
    }
}
