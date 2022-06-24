<?php

declare(strict_types=1);

namespace App\Command;

use App\Box;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class VersionCommand extends HyperfCommand
{

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('version');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Dump the version of box.');
    }

    public function handle()
    {
        $this->output->writeln([
            'box version: v' . Box::VERSION,
        ]);
    }

}
