<?php

namespace App\Exceptions;

use RuntimeException;

class AttendanceOnApprovedLeaveException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot check-in while on approved leave.');
    }
}
