<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add permission logic if needed
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        // dd($this);
        return [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$userId}",
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function update(User $user)
    {
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password ? Hash::make($this->password) : $user->password,
            'role' => $this->role,
            'status' => $this->status,
        ]);

        if ($this->roles) { // $this->roles is the single role ID from the form
            $role = Role::find($this->roles);
            if ($role) {
                // syncRoles removes old roles and assigns the new one
                $user->syncRoles([$role]);
            }
        }
    }
}
