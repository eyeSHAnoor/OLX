<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Service;
use App\Models\UserLogistic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRolePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')
                    ->where(fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))
                    ->ignore($this->id),
            ],
            // 'description' => ['required'],
            'permissions' => ['nullable', 'array',],
        ];
        return $rules;
    }

    public function update(Role $role)
    {
        $role->update([
            //            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            // 'description' => $this->description,
        ]);

        // Sync permissions (optional empty array)
        $role->syncPermissions($this->permissions ?? []);
    }
}
