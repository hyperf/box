<?php

namespace App\DownloadHandler;


use App\Config;
use App\GithubClient;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use SplFileInfo;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Swow\Debug\var_dump_return;

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
        $this->runtimePath = $this->config->getConfig('path.runtime', getenv('HOME') . '/box/runtime');
    }

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

    protected function download(string $url, string $savePath, int $permissions, array $context = []): SplFileInfo
    {
        $this->logger->info(sprintf('Downloading %s', $url));
        if (str_ends_with($savePath, '/')) {
            $explodedUrl = explode('/', $url);
            $filename = end($explodedUrl);
            $savePath = $savePath . $filename;
        }
        $mergeContextArr = array_merge([
            'ssl' => [
                'verify_peer' => false,
            ]
        ], $context);
        $context = stream_context_create($mergeContextArr);
        $remoteFile = fopen($url, 'r', false, $context);
        $localFile = fopen($savePath . '.tmp', 'w');
        /** @var OutputInterface $output */
        $output = Context::get(OutputInterface::class);
        $progressBar = new ProgressBar($output);
        while (! feof($remoteFile)) {
            fwrite($localFile, fread($remoteFile, 8192));
            $progressBar->advance(8192);
        }
        $progressBar->display();
        $output->writeln('');
        fclose($remoteFile);
        fclose($localFile);
        rename($savePath . '.tmp', $savePath);
        chmod($savePath, $permissions);
        $this->logger->info(sprintf('Download saved to %s', $savePath));
        return new SplFileInfo($savePath);
    }

    abstract public function handle(string $repo, string $version, array $options = []): ?SplFileInfo;
}