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

use App\Config;
use Hyperf\Command\Annotation\Command;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

#[Command]
class HyperfCommand extends AbstractCommand
{
    protected Config $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('hyperf');
        $this->config = $this->container->get(Config::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('The hyperf proxy command.');
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $path = $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        $currentPhpVersion = $this->config->getConfig('versions.php', '8.1');
        $hyperfBin = $this->config->getConfig('hyperf.bin', './bin/hyperf.php');
        if ($kernel === 'swoole') {
            $closeShortname = "-d swoole.use_shortname='Off'";
            $bin = $path . '/swoole-cli ' . $closeShortname . ' ' . $hyperfBin;
            if ($currentPhpVersion < '8.1') {
                $this->logger->warning(sprintf('Current setting PHP version is %s, but the kernel is Swoole and Swoole only support 8.1, so the PHP version is forced to 8.1.', $currentPhpVersion));
            }
        } else {
            $bin = $path . '/php' . $currentPhpVersion . ' ' . $hyperfBin;
        }
        $command = Str::replaceFirst('hyperf ', '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->liveCommand($fullCommand);
    }
}
