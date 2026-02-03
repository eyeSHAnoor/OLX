<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add permission logic if needed
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?? null,
            'password' => Hash::make($this->password),
            'status' => $this->status,
        ]);

        $role = Role::find($this->roles);
        if ($role) {
            $user->assignRole($role);
        }

    }
}
