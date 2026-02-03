<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use function Symfony\Component\Translation\t;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

//        $user = $this->route('category'); // You must bind this route model or pass it explicitly
//
//        if ($user) {
//            // If category exists, we're updating
//            return $this->category()->can('update', $user);
//        }
//
//        // Otherwise, we're creating
//        return $this->category()->can('create', User::class);

    }

    public function rules(): array
    {
//        dd($this->bankDetails);

        return [
            'name' => 'required|string',
            'email' => 'required|email',

            'display_name' => 'required|string',
            'designation' => 'required|string',
            'nic_unit' => 'required|in:nic,passport',
            'nic_no' => 'required',
            'contact_number' => 'required|string',
//            'address' => 'required|string',
            'dob' => 'required',
            'gender' => 'required|in:male,female,others',
//            'religion' => 'required|string',
            'date_joined' => 'required|date',
//            'other_duties' => 'required',
//            'verified_at' => 'required|string',
            'reports_to' => 'required',
            'branch_id' => 'required',
            'salaries' => ['nullable', 'array'],
            'salaries.*.salary_date' => ['required_with:salaries.*.net_salary', 'date'],
            'salaries.*.net_salary' => ['required_with:salaries.*.salary_date', 'numeric'],
        ];
    }

    public function saveRecord(): User
    {
        $this->merge([
            'created_by' => auth()->id(),
        ]);

        return DB::transaction(function () {

            if (!$this->new_password || !$this->confirm_new_password) {
                throw ValidationException::withMessages([
                    'new_password' => 'The password is required.',
                    'confirm_new_password' => 'The confirmation password is required.',
                ]);
            }

            if ($this->new_password !== $this->confirm_new_password) {
                throw ValidationException::withMessages([
                    'new_password' => 'The password confirmation does not match.',
                ]);
            }

            $this->merge([
                'password' => Hash::make($this->new_password),
            ]);

            $user = User::create($this->all());

            $role = Role::find($this->role_id);
            if (!$role) {
                throw ValidationException::withMessages([
                    'role_id' => 'Role not found',
                ]);
            }
            $user->syncRoles($role);


            // upload CV
            if ($this->hasFile('cv_document')) {
                $user->files?->where('collection', 'cv')?->each->delete();
                $user->addFiles(input: 'cv_document', collection: 'cv');
            }


            if ($this->bankDetails) {
                $user->bankDetails()->updateOrCreate(['user_id' => $user->id],
                    [
                        'account_number' => $this->bankDetails['account_number'],
                        'bank_name' => $this->bankDetails['bank_name'],
                        'branch_name' => $this->bankDetails['branch_name'],
                        'branch_code' => $this->bankDetails['branch_code'],
                    ]
                );
            }

            if ($this->salaries) {
                $user->salaries?->each->delete();
                foreach ($this->salaries as $salary) {
                    $user->salaries()->create([
                        'salary_date' => $salary['salary_date'],
                        'net_salary' => (float)$salary['net_salary'],
                    ]);
                }
            }

            return $user;

        });

    }

    public function updateRecord(User $user): bool|User
    {
        return DB::transaction(function () use ($user) {

            if ($this->new_password) {
                if ($this->new_password !== $this->confirm_new_password) {
                    throw ValidationException::withMessages([
                        'new_password' => 'The password confirmation does not match.',
                    ]);
                }

                $this->merge([
                    'password' => Hash::make($this->new_password),
                ]);
            }

            $role = Role::find($this->role_id);
            if (!$role) {
                throw ValidationException::withMessages([
                    'role_id' => 'Role not found',
                ]);
            }
            $user->syncRoles($role);

            // upload CV
            if ($this->hasFile('cv_document')) {
                $user->files?->where('collection', 'cv')?->each->delete();
                $user->addFiles(input: 'cv_document', collection: 'cv');
            }


            if ($this->bankDetails) {
                $user->bankDetails()->updateOrCreate(['user_id' => $user->id],
                    [
                        'account_number' => $this->bankDetails['account_number'],
                        'bank_name' => $this->bankDetails['bank_name'],
                        'branch_name' => $this->bankDetails['branch_name'],
                        'branch_code' => $this->bankDetails['branch_code'],
                    ]
                );
            }

            if ($this->salaries) {
                $user->salaries?->each->delete();
                foreach ($this->salaries as $salary) {
                    $user->salaries()->create([
                        'salary_date' => $salary['salary_date'],
                        'net_salary' => (float)$salary['net_salary'],
                    ]);
                }
            }


            return $user->update($this->all());
        });
    }
}
