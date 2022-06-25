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
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\Inject;

abstract class AbstractCommand extends HyperfCommand
{

    #[Inject]
    protected Config $config;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function liveCommand(string $command)
    {
        $handle = popen($command, 'r');
        while (! feof($handle)) {
            $data = fgets($handle);
            echo $data;
        }
        pclose($handle);
    }

    protected function getRuntimePath(): string
    {
        return $this->config->getConfig('path.runtime', getenv('HOME') . '/.box');
    }

    protected function getCurrentPhpVersion(): string
    {
        return $this->config->getConfig('versions.php', '8.1');
    }

}
