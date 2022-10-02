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

use App\Config;
use App\Exception\NotSupportVersionsException;
use App\GithubClient;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractDownloadHandler
{
    #[Inject]
    protected GithubClient $githubClient;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    #[Inject]
    protected Config $config;

    protected string $runtimePath;

    public function __construct()
    {
        $this->runtimePath = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
    }

    abstract public function handle(string $repo, string $version, array $options = []): ?SplFileInfo;

    abstract public function versions(string $repo, array $options = []): array;

    protected function fetchDownloadUrlFromGithubRelease(string $assetName, string $fullRepo, string $version): ?string
    {
        $release = $this->githubClient->getRelease($fullRepo, $version);
        $url = null;
        foreach ($release['assets'] ?? [] as $asset) {
            if ($asset['name'] === $assetName && $asset['browser_download_url']) {
                $url = $asset['browser_download_url'];
            }
        }
        return $url;
    }

    protected function fetchVersionsFromGithubRelease(string $fullRepo, ?string $assetName = null): array
    {
        $releases = $this->githubClient->getReleases($fullRepo);
        $versions = [];
        // Sort releases by published_at desc, also ignore error.
        usort($releases, function ($a, $b) {
            return strtotime($b['published_at'] ?? '0') <=> strtotime($a['published_at'] ?? '0');
        });
        // Filter releases which has assets, if the asset name is not null, then validate its.
        foreach ($releases as $release) {
            if (! empty($release['assets'])) {
                if ($assetName) {
                    foreach ($release['assets'] as $asset) {
                        if ($asset['name'] === $assetName) {
                            $versions[] = $release['tag_name'];
                        }
                    }
                } else {
                    $versions[] = $release['tag_name'];
                }
            }
        }
        return $versions;
    }

    protected function download(
        ?string $url,
        string $savePath,
        int $permissions,
        string $renameTo = ''
    ): SplFileInfo {
        if (! $url) {
            throw new \RuntimeException('Download url is empty, maybe the url parse failed.');
        }
        $this->logger->info(sprintf('Downloading %s', $url));
        if (str_ends_with($savePath, '/')) {
            $explodedUrl = explode('/', $url);
            $filename = end($explodedUrl);
            $savePath = $savePath . $filename;
        }
        $sink = $savePath . '.tmp';

        $channel = new Channel(1);
        $client = new Client([
            'sink' => $sink,
            'progress' => static function (
                $downloadTotal,
                $downloadedBytes,
            ) use ($channel) {
                $channel->push([$downloadedBytes, $downloadTotal]);
            },
        ]);

        /** @var OutputInterface $output */
        $output = Context::get(OutputInterface::class);
        $this->showProgressBar($output, $channel);

        $client->get($url);

        $output->writeln('');
        $output->writeln('');
        if ($renameTo) {
            $explodedSavePath = explode('/', $savePath);
            $filename = end($explodedSavePath);
            rename($sink, $afterRenameSavePath = Str::replaceLast($filename, $renameTo, $savePath));
            $savePath = $afterRenameSavePath;
        } else {
            rename($sink, $savePath);
        }
        chmod($savePath, $permissions);
        $this->logger->info(sprintf('Download saved to %s', $savePath));
        return new SplFileInfo($savePath);
    }

    protected function showProgressBar(OutputInterface $output, Channel $channel): void
    {
        Coroutine::create(function () use ($output, $channel) {
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('%current% kb [%bar%]');
            while ([$downloadedBytes, $downloadTotal] = $channel->pop(-1)) {
                if ($downloadTotal && $progressBar->getMaxSteps() !== $downloadTotal) {
                    $progressBar->setMaxSteps($this->byteToKb($downloadTotal));
                    $progressBar->setFormat('%current% kb / %max% kb [%bar%] %percent:3s%% %elapsed:6s% / %estimated:-6s%');
                }
                $downloadedBytes && $progressBar->setProgress($this->byteToKb($downloadedBytes));
                if ($downloadTotal && $downloadedBytes >= $downloadTotal) {
                    break;
                }
            }
            $progressBar->display();
            $progressBar->finish();
            $channel->close();
        });
    }

    protected function byteToKb(int $byte): int
    {
        return (int)ceil($byte / 1024);
    }
}
