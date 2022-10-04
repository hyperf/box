<?php

namespace App\Exception;


class PkgDefinitionNotFoundException extends BoxException
{
    public function __construct(string $pkgName)
    {
        parent::__construct(sprintf('The package %s definition not found', $pkgName));
    }
}