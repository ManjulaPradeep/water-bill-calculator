<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\MeterList as MerterListRequest;

class MeterController extends Controller
{
    /**
     * List all meters for a route.
     */
    public function list(MerterListRequest $request)
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
            $response = Http::withBasicAuth(config('api.auth_user'), config('api.auth_pass'))
                ->withHeaders(['Content-Type' => 'application/json'])
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

    /**
     * Update meter reading and generate bill.
     */
    public function update(Request $request)
    {
        $request->validate([
            'role_id'      => 'required|string',
            'route_id'     => 'required|string',
            'bcode'        => 'required',
            'pre_reading'  => 'required|numeric',
            'now_reading'  => 'required|numeric',
            'gps_lat'      => 'required|numeric',
            'gps_lng'      => 'required|numeric',
            'gps_accuracy' => 'required|numeric',
        ]);

        $user = Session::get('user');
        if (!$user) {
            return response()->json(['Status' => 401, 'Message' => 'Unauthenticated']);
        }

        $body = [
            'user_id'      => $user['user_name'],
            'session_id'   => Session::getId(),
            'role_id'      => $request->role_id,
            'route_id'     => $request->route_id,
            'pre_reading'  => $request->pre_reading,
            'bcode'        => $request->bcode,
            'now_reading'  => $request->now_reading,
            'accuracy'     => $request->gps_accuracy,
            'latitute'     => $request->gps_lat,
            'longitude'    => $request->gps_lng,
        ];

        try {
            $response = Http::withBasicAuth(config('api.auth_user'), config('api.auth_pass'))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(config('api.base_url') . '/V2/process/update_meter/', $body);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Update meter API error: ' . $e->getMessage());
            return response()->json(['Status' => 500, 'Message' => 'API error']);
        }
    }
}
