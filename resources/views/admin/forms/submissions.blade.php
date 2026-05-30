@extends('layouts.admin')

@section('title', __('Submissions'))

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Submissions') }}: {{ $form->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Read all submitted form entries.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.forms.submissions.clear', $form) }}" onsubmit="return confirm('{{ __('Clear all submissions for this form?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-red-300 text-red-600 dark:text-red-400 rounded-lg">
                    <i class="fas fa-trash"></i>
                    {{ __('Clear submissions') }}
                </button>
            </form>
            <a href="{{ route('admin.forms.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-dark-600 rounded-lg">
                <i class="fas fa-arrow-left"></i>
                {{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('IP Address') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Content') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-700">
                    @forelse($submissions as $submission)
                        <tr class="align-top">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $submission->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $submission->ip_address ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @php($payload = (array) ($submission->payload ?? []))
                                @if($payload === [])
                                    <span class="text-gray-500">—</span>
                                @else
                                    <div class="space-y-1">
                                        @foreach($payload as $key => $value)
                                            <div>
                                                <span class="font-semibold">{{ $key }}:</span>
                                                <span class="text-gray-700 dark:text-gray-300">{{ is_scalar($value) ? (string) $value : json_encode($value) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('No submissions yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-700">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
@endsection

