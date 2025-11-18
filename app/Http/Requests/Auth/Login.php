<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class Login extends FormRequest
{
    public function rules()
    {
        return [
            'user_name' => 'required|string',
            'password' => 'required|string',
            'finger_print' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'user_name' => 'user name',
        ];
    }

}
