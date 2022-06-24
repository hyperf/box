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

use App\Config;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildSelfCommand extends AbstractCommand
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
        $currentPhpVersion = $this->config->getConfig('versions.php', '8.1');
        $binPath = $this->config->getConfig('path.bin', getenv('HOME') . '/.box');
        $composer = $runtimePath . '/composer.phar';
        $php = $runtimePath . '/php' . $currentPhpVersion;
        $micro = $runtimePath . '/micro_php' . $currentPhpVersion . '.sfx';
        $boxBin = $binPath . '/box';
        $fastMode = $this->input->getOption('fast');
        $fastMode = $fastMode !== false;
        $composerUpadteCmd = '';
        if (! $fastMode) {
            $composerUpadteCmd = sprintf('%s %s update -oW --no-dev && ', $php, $composer);
        }
        $fullCommand = sprintf(
            'cd src && ' .
             $composerUpadteCmd .
             '%s -d phar.readonly=Off bin/hyperf.php phar:build &&
             cat %s ./box.phar > %s',
            $php,
            $micro,
            $boxBin
        );
        $this->liveCommand($fullCommand);
        chmod($boxBin, 0755);
        $this->output->info('Box build finished, saved to ' . $boxBin);
    }
}
