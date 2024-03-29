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
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class BuildPrepareCommand extends HyperfCommand
{
    protected Config $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('build-prepare');
        $this->config = $container->get(Config::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Prepare the enviroments for build the box cli.');
        $this->addOption('refresh', 'r', InputOption::VALUE_OPTIONAL, '', false);
    }

    public function handle()
    {
        $refresh = $this->input->getOption('refresh');
        $refresh = $refresh !== false;
        $runtimePath = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $currentPhpVersion = $this->config->getConfig('versions.php', '8.1');
        $composer = $runtimePath . '/composer.phar';
        $php = $runtimePath . '/php' . $currentPhpVersion;
        $micro = $runtimePath . '/micro_php' . $currentPhpVersion . '.sfx';
        $getCommand = $this->getApplication()->find('get');
        if (! file_exists($composer) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'composer']), $this->output);
        }
        if (! file_exists($php) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'php@' . $currentPhpVersion]), $this->output);
        }
        if (! file_exists($micro) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'micro@' . $currentPhpVersion]), $this->output);
        }

        $this->output->info('It is ready to build, try it now.');
    }
}
