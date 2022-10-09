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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildCommand extends AbstractCommand
{
    public function configure()
    {
        parent::configure();
        $this->setName('build');
        $this->setDescription('Build the application as a bin.');
        $this->addArgument('path', InputArgument::OPTIONAL, 'The path wants to build.', '.' . DIRECTORY_SEPARATOR);
        $this->addOption('name', '', InputOption::VALUE_OPTIONAL, 'The name of the output bin.', 'hyperf');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The output path of bin.', '.');
        $this->addOption('dev', 'd', InputOption::VALUE_NEGATABLE, 'Require the dev composer packages or not.', true);
    }

    public function handle()
    {
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        if ($kernel === 'swoole') {
            $this->logger->error('The build command is not supported in Swoole kernel.');
            return;
        }
        $path = $this->input->getArgument('path');
        $binName = $this->input->getOption('name');
        $outputPath = $this->input->getOption('output');
        $runtimePath = $this->getRuntimePath();
        $currentPhpVersion = $this->getCurrentPhpVersion();
        $extension = '';
        if (PHP_OS_FAMILY === 'Windows') {
            $extension = '.exe';
        }
        $composer = $runtimePath . DIRECTORY_SEPARATOR . 'composer.phar';
        $php = $runtimePath . DIRECTORY_SEPARATOR . 'php' . $currentPhpVersion . $extension;
        $micro = $runtimePath . DIRECTORY_SEPARATOR . 'micro_php' . $currentPhpVersion . '.sfx';
        if (! file_exists($composer) || ! file_exists($php) || ! file_exists($micro)) {
            $this->output->error('The build environment is broken, run `box build-prepare` command to make it ready.');
            return static::FAILURE;
        }
        $outputBin = $outputPath . DIRECTORY_SEPARATOR . $binName . $extension;
        $composerNoDevCmd = $this->buildComposerNoDevCommand($php, $composer);
        if (PHP_OS_FAMILY === 'Windows') {
            $fullCommand = sprintf(
                'cd %s && ' .
                $composerNoDevCmd .
                '%s -d phar.readonly=Off .\bin\hyperf.php phar:build --name=box-build.phar.tmp && cd .. && COPY /b %s + .\src\box-build.phar.tmp %s /y && rd /s /q .\src\box-build.phar.tmp',
                $path,
                $php,
                $micro,
                $outputBin
            );
        } else {
            $fullCommand = sprintf(
                'cd %s && ' .
                $composerNoDevCmd .
                '%s -d phar.readonly=Off ./bin/hyperf.php phar:build --name=box-build.phar.tmp && cd .. && cat %s ./src/box-build.phar.tmp > %s && rm -rf ./src/box-build.phar.tmp',
                $path,
                $php,
                $micro,
                $outputBin
            );
        }
        $this->liveCommand($fullCommand);
        if (file_exists($outputBin)) {
            $this->output->success(sprintf('The application %s is built successfully.', $outputBin));
            chmod($outputBin, 0755);
        } else {
            $this->output->error(sprintf('The application %s is built failed.', $outputBin));
        }
    }

    protected function buildComposerNoDevCommand(string $php, string $composer): string
    {
        $devMode = $this->input->getOption('dev');
        $composerNoDevCmd = '';
        if (! $devMode) {
            $composerNoDevCmd = sprintf('%s %s install -o --no-dev && ', $php, $composer);
        }
        return $composerNoDevCmd;
    }
}
