<?php

declare(strict_types=1);

namespace App\Command;

use App\Box;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class DebugCommand extends HyperfCommand
{

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('debug');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Debug.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $this->output->writeln([
            'box version: v' . Box::VERSION,
        ]);
    }

}
