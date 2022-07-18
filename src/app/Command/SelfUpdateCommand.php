<?php

namespace App\Command;

use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;

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
        $command = $this->getApplication()->find('get');
        $arguments = [
            'pkg' => 'box@latest',
        ];

        return $command->run(new ArrayInput($arguments), $output);
    }
}
