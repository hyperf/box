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
class PhpCommand extends AbstractCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('php');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('The php proxy command.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $path = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        $currentPhpVersion = $this->config->getConfig('versions.php', '8.1');
        if ($kernel === 'swoole') {
            $bin = $path . '/swoole-cli';
            if ($currentPhpVersion < '8.1') {
                $this->logger->warning(sprintf('Current setting PHP version is %s, but the kernel is Swoole and Swoole only support 8.1, so the PHP version is forced to 8.1.', $currentPhpVersion));
            }
        } else {
            $bin = $path . '/php' . $currentPhpVersion;
        }
        $command = Str::replaceFirst('php ', '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->liveCommand($fullCommand);
    }
}
