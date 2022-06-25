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

use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildSelfCommand extends BuildCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('build-self');
    }

    public function configure()
    {
        $this->setDescription('Build the application as a bin.');
        $this->addArgument('path', InputArgument::OPTIONAL, 'The path wants to build.', './src');
        $this->addOption('name', '', InputOption::VALUE_OPTIONAL, 'The name of the output bin.', 'box');
        $defaultOutputPath = $this->config->getConfig('path.bin', getenv('HOME') . '/.box');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The output path of bin.', $defaultOutputPath);
        $this->addOption('dev', 'd', InputOption::VALUE_NEGATABLE, 'Require the dev composer packages or not.', true);
    }

    protected function buildComposerNoDevCommand(string $php, string $composer): string
    {
        $devMode = $this->input->getOption('dev');
        $composerNoDevCmd = '';
        if (! $devMode) {
            $composerNoDevCmd = sprintf('%s %s update -o --no-dev && ', $php, $composer);
        }
        return $composerNoDevCmd;
    }
}
