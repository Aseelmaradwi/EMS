<?php

namespace App\Exceptions;

use RuntimeException;

class LeaveAccessDeniedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Forbidden.');
    }
}
