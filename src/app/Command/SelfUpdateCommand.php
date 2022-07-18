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
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Command()]
class SelfUpdateCommand extends SymfonyCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('self-update');
        $this->setDescription('Updates box to the latest version.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $application->setAutoExit(false);
        $arguments = [
            'command' => 'get',
            'pkg' => 'box@latest',
        ];

        return $application->run(new ArrayInput($arguments), $output);
    }
}
