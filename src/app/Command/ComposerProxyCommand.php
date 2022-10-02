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

#[Command]
class ComposerProxyCommand extends AbstractPhpCallProxyCommand
{
    protected string $proxyCommand = 'composer';

    protected string $proxyBin = 'composer.phar';
}
