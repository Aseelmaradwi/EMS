<?php

namespace App\Exceptions;

use RuntimeException;

class LeaveNotPendingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Only pending leave requests can be modified.');
    }
}
