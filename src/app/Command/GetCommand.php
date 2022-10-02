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

namespace App\Command;

use App\DownloadManager;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Context\Context;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Command]
class GetCommand extends HyperfCommand
{
    protected DownloadManager $downloadManager;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('get');
        $this->downloadManager = $this->container->get(DownloadManager::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Get the runtime or library into your project.');
        $this->addArgument('pkg', InputArgument::REQUIRED, 'The package name');
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, 'The download source.');
        $this->addOption('versions', '', InputOption::VALUE_NONE, 'Print the package versions only.');
    }

    public function handle()
    {
        Context::set(InputInterface::class, $this->input);
        Context::set(OutputInterface::class, $this->output);
        $pkg = $this->input->getArgument('pkg');
        $source = $this->input->getOption('source');
        $versions = $this->input->getOption('versions');
        [$pkg, $version] = $this->parsePkgVersion($pkg);
        $options = [];
        if ($source) {
            $options['source'] = $source;
        }
        if ($versions) {
            $versions = $this->downloadManager->versions($pkg, $options);
            $this->output->writeln($versions);
        } else {
            $this->downloadManager->get($pkg, $version, $options);
        }
    }

    protected function parsePkgVersion(string $pkg): array
    {
        $version = 'latest';
        if (str_contains($pkg, '@')) {
            [$pkg, $version] = explode('@', $pkg);
        }
        return [$pkg, $version];
    }
}
