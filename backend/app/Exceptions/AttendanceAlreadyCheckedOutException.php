<?php

namespace App\Exceptions;

use RuntimeException;

class AttendanceAlreadyCheckedOutException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Employee has already checked out for today.');
    }
}
