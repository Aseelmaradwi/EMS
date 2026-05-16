<?php

namespace App\Exceptions;

use RuntimeException;

class AttendanceAccessDeniedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Forbidden.');
    }
}
