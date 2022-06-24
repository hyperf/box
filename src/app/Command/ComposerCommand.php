<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

#[Command]
class ComposerCommand extends AbstractCommand
{

    protected Config $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('composer');
        $this->config = $this->container->get(Config::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('The composer proxy command.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $path = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $bin = $path . '/php8.1 ' . $path . '/composer.phar';
        $command = Str::replaceFirst('composer ', '', (string)$this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->proxyCommand($fullCommand);
    }

}
