{{-- @extends('layouts.default')

@section('title', __('Dashboard'))

@section('content')
    <div>
        <h1 class="text-2xl font-bold mb-4">{{ __('Dashboard') }}</h1>

        <!-- Pass Role ID and Route ID from session -->
<meter-list
    v-bind:role-id="'{{ session('user.role_id') }}'"
    v-bind:route-id="'{{ session('user.route_id') }}'"
    v-bind:translations="{{ json_encode([
        'account_number' => __('general.account_number'),
        'customer_name' => __('general.customer_name'),
        'previous_month_deficit' => __('general.previous_moth_dificit'),
        'periodic_charges' => __('general.periodic_charges'),
        'normal_points_count' => __('general.normal_points_count'),
        'pre_reading' => __('general.pre_reading'),
        'used_units' => __('general.used_units'),
        'new_reading' => __('general.new_reading'),
    ]) }}"
    v-on:selected="fillMeterData"
></meter-list>
        <!-- Inputs to be auto-filled -->
        <div v-if="selectedMeter" class="mt-6">
            <label class="block mb-1 font-medium">{{ __('general.customer_name') }}</label>
            <input type="text" v-model="selectedMeter.PayeeName" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.normal_points_count') }}</label>
            <input type="text" v-model="selectedMeter.AvgUnit" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.previous_moth_dificit') }}</label>
            <input type="text" v-model="selectedMeter.LastMonthDue" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.periodic_charges') }}</label>
            <input type="text" v-model="selectedMeter.Installment" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.pre_reading') }}</label>
            <input type="text" v-model="selectedMeter.PreReading" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.used_units') }}</label>
            <input type="text" v-model="selectedMeter.UsedUnits" class="w-full border px-3 py-2 rounded mb-2">

            <label class="block mb-1 font-medium">{{ __('general.new_reading') }}</label>
            <input type="text" v-model="selectedMeter.NewReading" class="w-full border px-3 py-2 rounded mb-2">
        </div>
    </div>
@endsection

@vite(['resources/js/app.js']) --}}

















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
