<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\CourseSetting;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'key' => 'required|string|max:255',
//            'value' => 'nullable'
        ];
    }


    public function updateRecord(): void
    {
//        dd($this->all());
        foreach ($this->all() as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
