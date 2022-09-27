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

        $chan = new Channel(1);
        $client = new Client([
            'sink' => $sink,
            'on_headers' => static function (ResponseInterface $response) use ($chan) {
                $chan->push($response->getHeaderLine('content-length'));
            },
        ]);

        /** @var OutputInterface $output */
        $output = Context::get(OutputInterface::class);
        $this->showProgressBar($output, $chan, $sink);

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

    protected function showProgressBar(OutputInterface $output, Channel $chan, string $sink): void
    {
        go(function () use ($output, $chan, $sink) {
            $max = $chan->pop(-1);
            $progressBar = new ProgressBar($output, (int) $max);
            $before = 0;
            while (true) {
                usleep(10000);
                if (file_exists($sink)) {
                    clearstatcache();
                    $size = filesize($sink);
                    $progressBar->advance(filesize($sink) - $before);
                    $before = $size;
                    if ($size >= $max) {
                        break;
                    }
                }
            }

            $progressBar->display();
        });
    }
}
