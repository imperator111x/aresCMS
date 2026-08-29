@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10 sm:py-14">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ $pageTitle }}</h1>
        @if($pageIntro !== '')
            <p class="text-gray-600 dark:text-gray-400 mb-8">{{ $pageIntro }}</p>
        @else
            <p class="text-gray-600 dark:text-gray-400 mb-8">{{ __('Send us a message. We will get back to you as soon as possible.') }}</p>
        @endif

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($form)
            {!! $formHtml !!}
            <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Protected against spam (honeypot + rate limit).') }}
            </p>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 text-amber-900 dark:text-amber-100 px-4 py-3 text-sm">
                {{ __('Contact form is not available.') }}
            </div>
        @endif
    </div>
@endsection
