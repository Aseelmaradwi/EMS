<?php

namespace App\Exceptions;

use RuntimeException;

class AttendanceCheckInRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot check-out before check-in.');
    }
}
