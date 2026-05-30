@extends('layouts.admin')

@section('title', __('Sponsor / Ad Slots'))

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Sponsor / Ad Slots') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Manage sponsor banners with impressions and clicks.') }}</p>
        </div>

        <div class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Create ad slot') }}</h2>
            <form method="POST" action="{{ route('admin.ad-slots.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="{{ __('Name') }}" required class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Internal name shown in admin only.') }}</p>
                </div>
                <div>
                    <select name="slot_key" required class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                        @foreach(($availableSlots ?? []) as $slotKey => $slotLabel)
                            <option value="{{ $slotKey }}">{{ $slotKey }} — {{ $slotLabel }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Defines where the ad appears on the site.') }} {{ __('Use one of the predefined slot keys shown above.') }}</p>
                </div>
                <div>
                    <input type="url" name="target_url" placeholder="https://example.com" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Destination when users click the ad.') }}</p>
                </div>
                <div>
                    <input type="url" name="image_url" placeholder="https://cdn.example.com/banner.jpg" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('External image URL for the banner (optional).') }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Upload banner image (optional)') }}</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Upload a local image file; preferred over image_url.') }} {{ __('PNG, JPG, GIF, WebP up to 5MB') }}</p>
                </div>
                <div>
                    <input type="number" name="priority" min="1" max="9999" value="100" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Default 100. Lower number = shown first (e.g. 10 before 100).') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="new_is_active" checked class="rounded border-gray-300">
                    <label for="new_is_active">{{ __('Active') }}</label>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('Only active ad slots can be displayed.') }}</span>
                </div>
                <div>
                    <input type="datetime-local" name="starts_at" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Optional time window for automatic start/stop.') }}</p>
                </div>
                <div>
                    <input type="datetime-local" name="ends_at" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Optional time window for automatic start/stop.') }}</p>
                </div>
                <div class="md:col-span-2">
                    <textarea name="html_code" rows="3" placeholder="{{ __('Optional HTML banner code') }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700"></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Fallback if no image is set (use trusted HTML only).') }}</p>
                </div>
                <div class="md:col-span-2">
                    <button class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold">{{ __('Create') }}</button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-dark-700 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left">slot_key</th>
                            <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Impressions') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Clicks') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                        @forelse($adSlots as $adSlot)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $adSlot->name }}</td>
                                <td class="px-4 py-3">{{ $adSlot->slot_key }}</td>
                                <td class="px-4 py-3">{{ $adSlot->is_active ? __('Active') : __('Disabled') }}</td>
                                <td class="px-4 py-3">{{ (int) $adSlot->impressions }}</td>
                                <td class="px-4 py-3">{{ (int) $adSlot->clicks }}</td>
                                <td class="px-4 py-3">
                                    <details>
                                        <summary class="cursor-pointer text-primary-600 dark:text-primary-400">{{ __('Edit') }}</summary>
                                        <form method="POST" action="{{ route('admin.ad-slots.update', $adSlot) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $adSlot->name }}" required class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                            <select name="slot_key" required class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                                @foreach(($availableSlots ?? []) as $slotKey => $slotLabel)
                                                    <option value="{{ $slotKey }}" @selected($adSlot->slot_key === $slotKey)>{{ $slotKey }} — {{ $slotLabel }}</option>
                                                @endforeach
                                            </select>
                                            <input type="url" name="target_url" value="{{ $adSlot->target_url }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                            <input type="url" name="image_url" value="{{ $adSlot->image_url }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">{{ __('Upload banner image (optional)') }}</label>
                                                <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                            </div>
                                            @if(!empty($adSlot->image_path))
                                                <div class="md:col-span-2">
                                                    <img src="{{ asset('storage/' . $adSlot->image_path) }}" alt="{{ $adSlot->name }}" class="max-h-24 rounded-lg border border-gray-200 dark:border-dark-700">
                                                </div>
                                            @endif
                                            <input type="number" name="priority" value="{{ (int) $adSlot->priority }}" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="is_active" value="1" id="active_{{ $adSlot->id }}" @checked($adSlot->is_active) class="rounded border-gray-300">
                                                <label for="active_{{ $adSlot->id }}">{{ __('Active') }}</label>
                                            </div>
                                            <textarea name="html_code" rows="2" class="md:col-span-2 px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700">{{ $adSlot->html_code }}</textarea>
                                            <div class="md:col-span-2">
                                                <button class="px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white">{{ __('Save') }}</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('admin.ad-slots.destroy', $adSlot) }}" class="mt-2">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white" onclick="return confirm('{{ __('Delete this ad slot?') }}')">{{ __('Delete') }}</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No ad slots yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $adSlots->links() }}</div>
    </div>
@endsection
