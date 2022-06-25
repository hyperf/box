<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildCommand extends AbstractCommand
{

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('build');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Build the application as a bin.');
        $this->addArgument('path', InputArgument::OPTIONAL, 'The path wants to build.', './');
        $this->addOption('name', '', InputOption::VALUE_OPTIONAL, 'The name of the output bin.', 'hyperf');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The output path of bin.', '.');
        $this->addOption('dev', 'd', InputOption::VALUE_NEGATABLE, 'Require the dev composer packages or not.', true);
    }

    public function handle()
    {
        $path = $this->input->getArgument('path');
        $binName = $this->input->getOption('name');
        $outputPath = $this->input->getOption('output');
        $runtimePath = $this->getRuntimePath();
        $currentPhpVersion = $this->getCurrentPhpVersion();
        $composer = $runtimePath . '/composer.phar';
        $php = $runtimePath . '/php' . $currentPhpVersion;
        $micro = $runtimePath . '/micro_php' . $currentPhpVersion . '.sfx';
        $outputBin = $outputPath . '/' . $binName;
        $devMode = $this->input->getOption('dev');
        $composerUpadteCmd = '';
        if (! $devMode) {
            $composerUpadteCmd = sprintf('%s %s install -o --no-dev && ', $php, $composer);
        }
        $fullCommand = sprintf(
            'cd %s && ' .
            $composerUpadteCmd .
            '%s -d phar.readonly=Off bin/hyperf.php phar:build --name=box-build.phar.tmp &&
             cat %s ./box-build.phar.tmp > %s && 
             rm -rf ./box-build.phar.tmp',
            $path,
            $php,
            $micro,
            $outputBin
        );
        $this->liveCommand($fullCommand);
        chmod($outputBin, 0755);
        $this->output->info('The application build finished, saved to ' . $outputBin);
    }
}
