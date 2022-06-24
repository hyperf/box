<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildSelfCommand extends HyperfCommand
{

    protected Config $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('build-self');
        $this->config = $container->get(Config::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Build the box cli.');
        $this->addOption('fast', 'f', InputOption::VALUE_OPTIONAL, '', false);
    }

    public function handle()
    {
        $runtimePath = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $binPath = $this->config->getConfig('path.bin', getenv('HOME') . '/.box');
        $composer = $runtimePath . '/composer.phar';
        $php = $runtimePath . '/php8.1';
        $micro = $runtimePath . '/micro_php8.1.sfx';
        $boxBin = $binPath . '/box';
        $fastMode = $this->input->getOption('fast');
        $fastMode = $fastMode !== false;
        $composerUpadteCmd = '';
        if (! $fastMode) {
            $composerUpadteCmd = '%s %s update -o -W --no-dev && ';
        }
        $fullCommand = sprintf(
            'cd src && ' .
             $composerUpadteCmd .
             '%s -d phar.readonly=Off bin/hyperf.php phar:build &&
             cat %s ./hyperf-cli.phar > %s && 
             rm -rf ./hyperf-cli.phar',
            $php, $composer, $php, $micro, $boxBin
        );
        $result = exec($fullCommand);
        chmod($boxBin, 0755);
        $result && $this->output->info('Box build finished, saved to ' . $boxBin);
    }

}
