@extends('layouts.admin')

@section('title', __('Server information'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Server information') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Technical runtime information of this installation.') }}</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-dark-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('System values') }}</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-dark-700">
                @foreach($info as $label => $value)
                    <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __(str_replace('_', ' ', ucfirst($label))) }}</span>
                        <code class="text-sm text-gray-900 dark:text-gray-100 break-all">{{ $value ?? 'n/a' }}</code>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-dark-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('PHP extensions') }}</h2>
            </div>
            <div class="p-5 space-y-2">
                @foreach($extensions as $ext => $loaded)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-dark-700 px-3 py-2">
                        <code class="text-sm text-gray-800 dark:text-gray-200">{{ $ext }}</code>
                        @if($loaded)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">{{ __('Loaded') }}</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">{{ __('Missing') }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

