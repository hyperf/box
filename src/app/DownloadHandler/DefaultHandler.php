<?php

namespace App\DownloadHandler;


use SplFileInfo;

class DefaultHandler extends AbstractDownloadHandler
{

    public function handle(string $repo, string $version, array $options = []): ?SplFileInfo
    {

    }
}