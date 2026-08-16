<?php

namespace App\Modules\Admin\Requests;

use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(SystemPermission::ViewUsers->value) ?? false;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['verified', 'unverified'])],
            'role' => ['nullable', Rule::in([RoleName::Admin->value, RoleName::SuperAdmin->value, 'none'])],
        ];
    }

    /** @return array{search: string, status: string, role: string} */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => (string) ($validated['status'] ?? ''),
            'role' => (string) ($validated['role'] ?? ''),
        ];
    }
}
