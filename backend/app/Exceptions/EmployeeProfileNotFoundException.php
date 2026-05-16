<?php

namespace App\Exceptions;

use RuntimeException;

class EmployeeProfileNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Employee profile not found for authenticated user.');
    }
}
