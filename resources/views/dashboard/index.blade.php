@extends('layouts.default')

@section('title', __('Dashboard'))

@section('content')
    <div>
        <dashboard
            role-id="{{ strval(session('user.role_id')) }}"
            route-id="{{ strval(session('user.route_id')) }}"
            :translations="{{ json_encode([
                'BCode' => __('general.account_number'),
                'PayeeName' => __('general.customer_name'),
                'LastMonthDue' => __('general.previous_moth_dificit'),
                'Installment' => __('general.periodic_charges'),
                'AvgUnit' => __('general.normal_points_count'),
                'PreReading' => __('general.pre_reading'),
                'UsedUnits' => __('general.used_units'),
                'NewReading' => __('general.new_reading'),
            ]) }}">
        </dashboard>
    </div>
@endsection

@vite(['resources/js/app.js'])
