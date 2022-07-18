<?php

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

class SelfUpdateCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('self-update');
    }

    public function handle()
    {
        $command = $this->getApplication()->find('get');
        $arguments = [
            'box@latest',
        ];

        return $command->run();
    }
}
