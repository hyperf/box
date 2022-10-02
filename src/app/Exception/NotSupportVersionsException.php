<?php

namespace App\Exception;

class NotSupportVersionsException extends BoxException
{
    public function __construct(string $repo)
    {
        parent::__construct(sprintf('Package %s is not support for `--versions` option.', $repo));
    }
}
