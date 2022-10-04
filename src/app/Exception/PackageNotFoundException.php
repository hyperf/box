<?php

namespace App\Exception;


class PackageNotFoundException extends BoxException
{
    public function __construct(string $pkgName)
    {
        parent::__construct(sprintf('The package %s for %s not found', $pkgName, PHP_OS));
    }
}