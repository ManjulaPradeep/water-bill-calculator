<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterList as Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class MeterList extends Controller
{
    public function list(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $body = [
            'user_id'    => $user['user_name'],
            'session_id' => Session::getId(),
            'role_id'    => $request->input('role_id'),
            'route_id'   => $request->input('route_id'),
        ];

        try {
            $response = Http::withBasicAuth(
                config('api.auth_user'),
                config('api.auth_pass')
            )
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(config('api.base_url') . '/V2/process/role_list/', $body);

            $data = $response->json();

            if (($data['Status'] ?? '') === '200') {
                return response()->json($data['Data']['Role_Process'] ?? []);
            }

            return response()->json(['message' => $data['Message'] ?? 'Failed'], 400);
        } catch (\Exception $e) {
            Log::error('Role list API error: ' . $e->getMessage());
            return response()->json(['message' => 'API request failed'], 500);
        }
    }
}
