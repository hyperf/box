<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Command]
class ComposerCommand extends HyperfCommand
{

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('composer');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('The composer proxy command.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $bin = '/usr/local/box/php8.1' . ' /usr/local/box/composer.phar';
        $command = Str::replaceFirst('composer ', '',  (string)$this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        var_dump($fullCommand);
        exec($fullCommand);
    }

}
