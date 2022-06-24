<?php

declare(strict_types=1);

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
        $composer = $runtimePath . '/composer.phar';
        $php = $runtimePath . '/php8.1';
        $micro = $runtimePath . '/micro_php8.1.sfx';
        $getCommand = $this->getApplication()->find('get');
        if (! file_exists($composer) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'composer']), $this->output);
        }
        if (! file_exists($php) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'php']), $this->output);
        }
        if (! file_exists($micro) || $refresh) {
            $getCommand->run(new ArrayInput(['pkg' => 'micro']), $this->output);
        }

        $this->output->info('Box build is prepared.');
    }

}
