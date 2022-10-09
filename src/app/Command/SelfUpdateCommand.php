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
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class SelfUpdateCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('self-update');
        $this->setDescription('Upgrade box to the latest version.');
    }

    public function configure()
    {
        $this->addOption('reinstall', 'r', InputOption::VALUE_NONE, 'Ignore the local file and reinstall.');
    }

    public function handle()
    {
        $this->call('get', ['pkg' => 'box@latest', '--reinstall' => $this->input->getOption('reinstall')]);
    }
}
