<?php

namespace App\Modules\Authorization\Exceptions;

use RuntimeException;

final class RoleManagementException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $field = 'name',
    ) {
        parent::__construct($message);
    }
}
