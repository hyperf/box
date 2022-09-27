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
use App\GithubClient;
use GuzzleHttp\Client;
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

    protected function fetchDownloadUrlFromGithubRelease(string $assetName, string $fullRepo, string $version): ?string
    {
        $release = $this->githubClient->getRelease($fullRepo, $version);
        $url = null;
        foreach ($release['assets'] ?? [] as $asset) {
            if ($asset['name'] === $assetName) {
                $url = $asset['browser_download_url'];
            }
        }
        return $url;
    }

    protected function download(
        string $url,
        string $savePath,
        int $permissions,
        string $renameTo = ''
    ): SplFileInfo {
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
            'progress' => static function(
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
            while ([$downloadedBytes, $downloadTotal] = $channel->pop(-1)) {
                if ($downloadTotal && $progressBar->getMaxSteps() !== $downloadTotal) {
                    $progressBar->setMaxSteps($downloadTotal);
                }
                $downloadedBytes && $progressBar->setProgress($downloadedBytes);
                if ($downloadTotal && $downloadedBytes >= $downloadTotal) {
                    break;
                }
            }
            $progressBar->display();
            $progressBar->finish();
            $channel->close();
        });
    }
}