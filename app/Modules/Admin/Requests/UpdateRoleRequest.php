<?php

namespace App\Modules\Admin\Requests;

use App\Modules\Authorization\PermissionCatalog;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(SystemPermission::ManageRoles->value) ?? false;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z][A-Za-z0-9 _-]*$/'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'distinct', Rule::in(app(PermissionCatalog::class)->names())],
            'updated_at' => ['required', 'date'],
        ];
    }
}
