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
use App\Exception\BoxException;
use Hyperf\Contract\StdoutLoggerInterface;
use RuntimeException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Command\Command as HyperfCommand;

abstract class AbstractCommand extends HyperfCommand
{
    #[Inject]
    protected Config $config;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    protected function liveCommand(string $command)
    {
        if ($this->isFunctionExists('passthru')) {
            passthru($command);
        } elseif ($this->isFunctionExists('proc_open')) {
            proc_open($command, [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR
            ], $pipes);
        } elseif ($this->isFunctionExists(['popen', 'pclose', 'feof', 'fgets'])) {
            $handle = popen($command, 'r');
            while (! feof($handle)) {
                $data = fgets($handle);
                echo $data;
            }
            pclose($handle);
        } else {
            throw new RuntimeException('No available function to run command.');
        }
    }

    protected function getRuntimePath(): string
    {
        $base = getenv('HOME') ?: getenv('USERPROFILE');
        return $this->config->getConfig('path.runtime', $base . DIRECTORY_SEPARATOR . '.box');
    }

    protected function getCurrentPhpVersion(): string
    {
        return $this->config->getConfig('versions.php', '8.1');
    }

    protected function isFunctionExists(string|array $functions): bool
    {
        $isExists = true;
        foreach ((array) $functions as $function) {
            if (! function_exists($function)) {
                $isExists = false;
                break;
            }
        }
        return $isExists;
    }

    protected function buildBinPath(): string
    {
        $path = $this->getRuntimePath();
        $kernel = strtolower($this->config->getConfig('kernel', 'swow'));
        $currentPhpVersion = $this->getCurrentPhpVersion();
        $os = PHP_OS_FAMILY;
        if ($kernel === 'swoole') {
            if ($os === 'Windows') {
                throw new BoxException('The command is not supported in Swoole kernel on Windows.');
            }
            if ($currentPhpVersion < '8.1') {
                $this->logger->warning(sprintf('Current setting PHP version is %s, but the kernel is Swoole and Swoole only support 8.1, so the PHP version is forced to 8.1.', $currentPhpVersion));
            }
            $bin = $path . DIRECTORY_SEPARATOR . 'swoole-cli';
        } else {
            $extension = '';
            if ($os === 'Windows') {
                $extension = '.exe';
            }
            $bin = $path . DIRECTORY_SEPARATOR . 'php' . $currentPhpVersion . $extension;
        }
        return $bin;
    }
}
