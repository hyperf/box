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

use App\Exception\BoxException;
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
        $bin = $this->buildBinPath();
        $command = Str::replaceFirst('php ', '', (string) $this->input);
        $fullCommand = sprintf('%s %s', $bin, $command);
        $this->liveCommand($fullCommand);
    }
}
