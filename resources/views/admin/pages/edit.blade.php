@extends('layouts.admin')

@section('title', __('Edit Page'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Page') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Build your page by adding blocks and reordering them.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.pages.form', ['submitLabel' => __('Update')])
    </form>

    @if(($revisions ?? collect())->count() > 0)
        <div class="mt-8 bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-5">
            <h2 class="text-lg font-semibold mb-4">{{ __('Versions') }}</h2>
            <div class="space-y-2">
                @foreach($revisions as $revision)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 rounded-lg border border-gray-200 dark:border-dark-700">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('Saved at') }}: {{ optional($revision->created_at)->format('d.m.Y H:i:s') }}
                        </div>
                        <form method="POST" action="{{ route('admin.pages.revisions.restore', [$page, $revision]) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg border border-primary-300 text-primary-600 dark:text-primary-400 text-xs">
                                {{ __('Restore version') }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

