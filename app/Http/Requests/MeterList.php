<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeterList extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role_id'  => 'required|string',
            'route_id' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => 'Role ID',
            'route_id' => 'Route ID',
        ];
    }
}
