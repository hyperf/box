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

use Hyperf\Utils\Str;

abstract class AbstractPhpCallProxyCommand extends AbstractCommand
{
    protected string $proxyCommand;

    protected string $proxyBin;

    public function configure()
    {
        $this->setDescription(sprintf('The proxy command of `php %s`.', $this->proxyCommand));
        $this->setName($this->proxyCommand);
        $this->ignoreValidationErrors();
    }

    public function handle()
    {
        $bin = $this->buildBinCommand();
        $command = Str::replaceFirst($this->proxyCommand, '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, trim($command));
        $this->liveCommand($fullCommand);
    }

    protected function buildBinCommand(): string
    {
        $path = $this->getRuntimePath();
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        $currentPhpVersion = $this->getCurrentPhpVersion();
        if ($kernel === 'swoole') {
            $bin = $path . '/swoole-cli';
            if ($currentPhpVersion < '8.1') {
                $this->logger->warning(sprintf('Current setting PHP version is %s, but the kernel is Swoole and Swoole only support 8.1, so the PHP version is forced to 8.1.', $currentPhpVersion));
            }
        } else {
            $bin = $path . '/php' . $currentPhpVersion;
        }
        return $bin . ' ' . $path . '/' . $this->proxyBin;
    }
}
