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

use Hyperf\Command\Command as HyperfCommand;

abstract class AbstractCommand extends HyperfCommand
{
    protected function liveCommand(string $command)
    {
        $handle = popen($command, 'r');
        while (! feof($handle)) {
            $data = fgets($handle);
            echo $data;
        }
        pclose($handle);
    }
}
