@extends('layouts.app')

@section('title', __('legal.terms.page_title'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-8">{{ __('legal.terms.heading') }}</h1>

        <div class="prose prose-gray dark:prose-invert max-w-none space-y-8 text-gray-700 dark:text-gray-300">
            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.terms.section1_title') }}</h2>
                <p>{{ __('legal.terms.section1_body') }}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.terms.section2_title') }}</h2>
                <p>{{ __('legal.terms.section2_body') }}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.terms.section3_title') }}</h2>
                <p>{{ __('legal.terms.section3_body') }}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.terms.section4_title') }}</h2>
                <p>{{ __('legal.terms.section4_body') }}</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('legal.terms.section5_title') }}</h2>
                <p>{{ __('legal.terms.section5_body') }}</p>
            </section>
        </div>
    </div>
@endsection

