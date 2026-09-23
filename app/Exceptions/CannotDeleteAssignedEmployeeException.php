<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class CannotDeleteAssignedEmployeeException extends RuntimeException
{
    public function __construct(string $message = 'Cannot delete an employee assigned to active or historical missions.')
    {
        parent::__construct($message);
    }
}
