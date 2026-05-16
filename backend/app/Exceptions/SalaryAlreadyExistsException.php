<?php

namespace App\Exceptions;

use RuntimeException;

class SalaryAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Salary already exists for this employee.');
    }
}
