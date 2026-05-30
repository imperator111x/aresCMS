@extends('layouts.admin')

@section('title', __('Comment Moderation AI'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Comment Moderation AI') }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Review comments flagged by AI before publishing.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Rule tuning') }}</h2>
            <form action="{{ route('admin.comment-moderation.settings') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Pending threshold') }}</label>
                    <input type="number" min="0" max="100" name="pending_threshold" value="{{ old('pending_threshold', (int) ($config['pending_threshold'] ?? 50)) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Auto reject threshold') }}</label>
                    <input type="number" min="0" max="100" name="reject_threshold" value="{{ old('reject_threshold', (int) ($config['reject_threshold'] ?? 80)) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Max links allowed') }}</label>
                    <input type="number" min="0" max="10" name="max_links" value="{{ old('max_links', (int) ($config['max_links'] ?? 1)) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-gray-100">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Blocked words (comma or newline separated)') }}</label>
                    <textarea name="toxic_words" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-gray-900 dark:text-gray-100">{{ old('toxic_words', implode(', ', $config['toxic_words'] ?? [])) }}</textarea>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold">
                        {{ __('Save moderation rules') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl overflow-hidden">
            @if($pendingComments->isEmpty())
                <div class="p-8 text-center text-gray-600 dark:text-gray-400">
                    <i class="fas fa-check-circle text-3xl text-emerald-500 mb-2"></i>
                    <p>{{ __('No pending comments right now.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-dark-700 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left">{{ __('Author') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('On article') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('Comment') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('Score') }}</th>
                                <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                            @foreach($pendingComments as $comment)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($comment->news)
                                            <a href="{{ route('news.show', $comment->news) }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                                                {{ $comment->news->title }}
                                            </a>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xl">{{ \Illuminate\Support\Str::limit($comment->content, 180) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                            {{ (int) ($comment->moderation_score ?? 0) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('admin.comment-moderation.approve', $comment) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">
                                                    {{ __('Approve') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.comment-moderation.reject', $comment) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold">
                                                    {{ __('Reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div>
            {{ $pendingComments->links() }}
        </div>
    </div>
@endsection
