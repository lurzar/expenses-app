<?php

namespace App\Modules\Authorization;

interface PermissionDefinition
{
    public function module(): string;

    public function labelKey(): string;

    public function descriptionKey(): string;

    public function scope(): PermissionScope;
}
