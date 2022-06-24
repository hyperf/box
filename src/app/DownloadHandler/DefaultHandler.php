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
namespace App\DownloadHandler;

use SplFileInfo;

class DefaultHandler extends AbstractDownloadHandler
{
    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {
    }
}
