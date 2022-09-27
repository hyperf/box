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
        $command = Str::replaceFirst($this->proxyCommand . ' ', '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->liveCommand($fullCommand);
    }

    protected function buildBinCommand(): string
    {
        $path = $this->getRuntimePath();
        return $path . '/php' . $this->getCurrentPhpVersion() . ' ' . $path . '/' . $this->proxyBin;
    }
}
