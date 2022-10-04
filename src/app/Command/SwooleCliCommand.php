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
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

#[Command]
class SwooleCliCommand extends AbstractPhpCallProxyCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('swoole-cli');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('The swoole-cli proxy command.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $path = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $bin = $path . '/swoole-cli';
        $command = Str::replaceFirst('swoole-cli ', '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->liveCommand($fullCommand);
    }
}
