@extends('layouts.admin')

@section('title', __('Scheduled jobs'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Scheduled jobs') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ __('Configure recurring maintenance tasks for the scheduler.') }}</p>
    </div>

    <div class="mb-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-800 dark:text-amber-200">
        <p class="font-semibold">{{ __('Important') }}</p>
        <p class="mt-1">{{ __('Server cron must run "php artisan schedule:run" every minute for these jobs to execute.') }}</p>
    </div>

    @if(session('schedule_job_output'))
        <div class="mb-4 p-4 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('Command output') }}</p>
            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ session('schedule_job_output') }}</pre>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.operations.schedule.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($jobs as $jobKey => $job)
            @php($jobConfig = $configs[$jobKey] ?? ['enabled' => false, 'frequency' => $job['default_frequency'], 'time' => $job['default_time'], 'day' => $job['default_day']])
            @php($status = $statuses[$jobKey] ?? [])
            <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $job['label'] }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $job['description'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-mono">php artisan {{ $job['command'] }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3 text-xs">
                            <div class="rounded-md bg-gray-50 dark:bg-dark-900 border border-gray-200 dark:border-dark-700 px-2 py-1">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Last run') }}:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $status['last_run_at'] ?? '—' }}</span>
                            </div>
                            <div class="rounded-md bg-gray-50 dark:bg-dark-900 border border-gray-200 dark:border-dark-700 px-2 py-1">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Last exit code') }}:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $status['last_exit_code'] ?? '—' }}</span>
                            </div>
                            <div class="rounded-md bg-gray-50 dark:bg-dark-900 border border-gray-200 dark:border-dark-700 px-2 py-1">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Last duration') }}:</span>
                                <span class="text-gray-900 dark:text-gray-100">
                                    @if(isset($status['last_duration_ms']) && $status['last_duration_ms'] !== null)
                                        {{ $status['last_duration_ms'] }} ms
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="jobs[{{ $jobKey }}][enabled]" value="0">
                            <input type="checkbox" name="jobs[{{ $jobKey }}][enabled]" value="1" class="rounded border-gray-300 dark:border-dark-600 text-primary-600 focus:ring-primary-500" {{ old("jobs.$jobKey.enabled", $jobConfig['enabled']) ? 'checked' : '' }}>
                            <span>{{ __('Enabled') }}</span>
                        </label>
                        <button
                            type="submit"
                            formaction="{{ route('admin.operations.schedule.run', $jobKey) }}"
                            formmethod="POST"
                            onclick="return confirm(@json(__('Run this job now?')));"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium"
                        >
                            <i class="fas fa-play"></i>
                            {{ __('Run now') }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Frequency') }}</label>
                        <select name="jobs[{{ $jobKey }}][frequency]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                            @foreach($frequencies as $frequencyKey => $frequencyLabel)
                                <option value="{{ $frequencyKey }}" {{ old("jobs.$jobKey.frequency", $jobConfig['frequency']) === $frequencyKey ? 'selected' : '' }}>{{ $frequencyLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Time (HH:MM)') }}</label>
                        <input type="text" name="jobs[{{ $jobKey }}][time]" value="{{ old("jobs.$jobKey.time", $jobConfig['time']) }}" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Weekday (for weekly)') }}</label>
                        <select name="jobs[{{ $jobKey }}][day]" class="w-full rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                            @foreach($days as $dayKey => $dayLabel)
                                <option value="{{ $dayKey }}" {{ old("jobs.$jobKey.day", $jobConfig['day']) === $dayKey ? 'selected' : '' }}>{{ $dayLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium">
            <i class="fas fa-save"></i>
            {{ __('Save scheduled jobs') }}
        </button>
    </form>
@endsection
