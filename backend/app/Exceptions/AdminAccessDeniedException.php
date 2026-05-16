<?php

namespace App\Exceptions;

use RuntimeException;

class AdminAccessDeniedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Forbidden. Admin access is required.');
    }
}
