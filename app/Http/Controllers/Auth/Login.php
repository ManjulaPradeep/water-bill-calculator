<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login as Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Exception;

class Login extends Controller
{
public function login(Request $request)
{
    $userId   = $request->input('user_name');
    $password = $request->input('password');
    $sessionId = Session::getId();

    $body = json_encode([
        'user_id'    => $userId,
        'password'   => $password,
        'session_id' => $sessionId,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    try {
        $response = Http::withBasicAuth(
            config('api.auth_user'),
            config('api.auth_pass')
        )
        ->withHeaders([
            'Content-Type' => 'text/plain',
        ])
        ->send('POST', config('api.base_url') . '/V2/user/login/', [
            'body' => $body
        ]);

        $rawBody = trim($response->body());
        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors([
                'user_name' => 'Login failed: Invalid API response.'
            ])->withInput();
        }

        if (($data['Status'] ?? '') === 200) {
            // Store user info in session
            Session::put('user', [
                'user_name' => $userId,
                'role_id'   => $data['Role_ID'] ?? null,
                'route_id'  => $data['Route_ID'] ?? null,
            ]);

            return redirect()->intended('/dashboard');
        }

        $message = $data['Message'] ?? 'Invalid credentials';
        return back()->withErrors(['user_name' => $message])->withInput();
    } catch (\Exception $e) {
        Log::error('Login API Exception: ' . $e->getMessage());
        return back()->withErrors(['user_name' => 'Login service unavailable'])->withInput();
    }
}


    public function logout()
    {
        Session::forget('user');
        Session::flush();
        Session::invalidate();
        Session::regenerateToken();
        return redirect()->route('auth.landing');
    }
}
