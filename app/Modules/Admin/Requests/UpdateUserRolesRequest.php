<?php

namespace App\Modules\Admin\Requests;

use App\Modules\Authorization\Permissions\SystemPermission;
use App\Modules\Authorization\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(SystemPermission::ManageUserRoles->value) ?? false;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'roles' => ['present', 'array', 'max:2'],
            'roles.*' => ['string', 'distinct', Rule::in([RoleName::Admin->value, RoleName::SuperAdmin->value])],
            'authorization_version' => ['required', 'integer', 'min:0'],
        ];
    }

    /** @return list<RoleName> */
    public function roles(): array
    {
        return array_values(array_map(
            static fn (string $role): RoleName => RoleName::from($role),
            $this->validated('roles'),
        ));
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $roles = $this->input('roles', []);

                if (is_array($roles) && count($roles) !== count(array_unique($roles))) {
                    $validator->errors()->add('roles', __('admin.roles_unique'));
                }
            },
        ];
    }
}
