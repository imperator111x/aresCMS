@extends('layouts.admin')

@section('title', __('Plugins'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Plugins') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Manage local CMS plugins from the plugins directory.') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <form action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data" class="mb-6">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <input type="file" name="plugin_zip" accept=".zip,application/zip" required class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 dark:file:bg-dark-700 file:text-gray-700 dark:file:text-gray-200">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-upload"></i>
                    {{ __('Upload plugin ZIP') }}
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Upload a plugin ZIP containing plugin.json in the plugin root.') }}</p>
        </form>

        @if(empty($plugins))
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('No plugins found.') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-dark-700 text-left text-gray-500 dark:text-gray-400">
                            <th class="py-3 pr-4">{{ __('Name') }}</th>
                            <th class="py-3 pr-4">{{ __('Status') }}</th>
                            <th class="py-3 pr-4">{{ __('Version') }}</th>
                            <th class="py-3 pr-4">{{ __('Details') }}</th>
                            <th class="py-3 pr-4">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                        @foreach($plugins as $plugin)
                            <tr>
                                <td class="py-3 pr-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $plugin['name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $plugin['slug'] }}</div>
                                </td>
                                <td class="py-3 pr-4">
                                    @if($plugin['enabled'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                            <i class="fas fa-check-circle text-xs"></i>{{ __('Enabled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-300">
                                            <i class="fas fa-ban text-xs"></i>{{ __('Disabled') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">
                                    {{ $plugin['version'] ?: '—' }}
                                </td>
                                <td class="py-3 pr-4">
                                    @if(!empty($plugin['errors']))
                                        <div class="text-xs text-red-600 dark:text-red-400">
                                            {{ implode(' ', $plugin['errors']) }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-600 dark:text-gray-300">
                                            {{ $plugin['description'] ?: '—' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if(!empty($plugin['directory']) && empty($plugin['errors']))
                                        <form action="{{ route('admin.plugins.toggle', ['directory' => $plugin['directory']]) }}" method="POST" class="inline-flex">
                                            @csrf
                                            <input type="hidden" name="enabled" value="{{ $plugin['enabled'] ? '0' : '1' }}">
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium {{ $plugin['enabled'] ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' }}">
                                                <i class="fas {{ $plugin['enabled'] ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                                {{ $plugin['enabled'] ? __('Disable') : __('Enable') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
@endsection

