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
class PhpCsFixerProxyCommand extends AbstractPhpCallProxyCommand
{
    protected string $proxyCommand = 'php-cs-fixer';

    protected string $proxyBin = 'php-cs-fixer.phar';
}
