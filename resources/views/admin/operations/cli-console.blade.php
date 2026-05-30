@extends('layouts.admin')

@section('title', __('CLI Console'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('CLI Console') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Run maintenance commands directly on the server (Owner only).') }}</p>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <p class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md px-3 py-2 mb-4">
            {{ __('Allowed commands: php artisan ..., php ..., composer ..., npm ...') }}
        </p>

        <form method="POST" action="{{ route('admin.operations.cli.execute') }}" class="space-y-4">
            @csrf
            <div>
                <label for="command" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Command') }}</label>
                <input
                    id="command"
                    name="command"
                    type="text"
                    value="{{ old('command', session('cli_command')) }}"
                    placeholder="php artisan optimize:clear"
                    class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    required
                >
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
                <i class="fas fa-play"></i>
                {{ __('Execute command') }}
            </button>
        </form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Common commands') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Click or copy these as a base for your command input.') }}</p>
            <div class="mt-4 rounded-lg border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-900 p-3">
                <ul class="space-y-1 text-xs font-mono text-gray-700 dark:text-gray-300">
                    @foreach($commonCliCommands as $command)
                        <li>{{ $command }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('All available Artisan commands (:count)', ['count' => count($artisanCommands)]) }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Generated live from this installation.') }}</p>
            <div class="mt-4 max-h-96 overflow-auto rounded-lg border border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-900 p-3">
                <ul class="space-y-1 text-xs font-mono text-gray-700 dark:text-gray-300">
                    @foreach($artisanCommands as $artisanCommand)
                        <li>{{ 'php artisan '.$artisanCommand }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    @if(session('cli_output'))
        <div class="mt-6 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Command output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('cli_output') }}</pre>
        </div>
    @endif
@endsection
