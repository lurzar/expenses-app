<?php

namespace App\Modules\Authorization\Exceptions;

use RuntimeException;

final class RoleAssignmentException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $field = 'roles',
    ) {
        parent::__construct($message);
    }
}
