<?php

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