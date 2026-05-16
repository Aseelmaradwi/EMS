<?php

namespace App\Exceptions;

use RuntimeException;

class LeaveOverlapException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Overlapping leave request already exists for this period.');
    }
}
