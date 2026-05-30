@extends('layouts.admin')

@section('title', __('Health check'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Health check') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Quick runtime checks for core services and production readiness.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-xs uppercase tracking-wider text-green-700 dark:text-green-300">{{ __('OK') }}</p>
            <p class="text-2xl font-bold text-green-800 dark:text-green-200">{{ $summary['ok'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
            <p class="text-xs uppercase tracking-wider text-amber-700 dark:text-amber-300">{{ __('Warnings') }}</p>
            <p class="text-2xl font-bold text-amber-800 dark:text-amber-200">{{ $summary['warn'] }}</p>
        </div>
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4">
            <p class="text-xs uppercase tracking-wider text-red-700 dark:text-red-300">{{ __('Failures') }}</p>
            <p class="text-2xl font-bold text-red-800 dark:text-red-200">{{ $summary['fail'] }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-dark-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Checks') }}</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-dark-700">
            @foreach($checks as $check)
                @php
                    $styles = match ($check['status']) {
                        'ok' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        'warn' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        default => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    };
                    $label = match ($check['status']) {
                        'ok' => __('OK'),
                        'warn' => __('Warning'),
                        default => __('Failure'),
                    };
                @endphp
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $check['name'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $check['message'] }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $styles }}">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
