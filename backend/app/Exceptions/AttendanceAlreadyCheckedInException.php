<?php

namespace App\Exceptions;

use RuntimeException;

class AttendanceAlreadyCheckedInException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Employee has already checked in for today.');
    }
}
