<?php

namespace App\Exceptions;

use RuntimeException;

class EmployeeAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Employee profile already exists for this user.');
    }
}
