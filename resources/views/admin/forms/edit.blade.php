@extends('layouts.admin')

@section('title', __('Edit Form'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit Form') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Build a reusable contact or inquiry form.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
        @csrf
        @method('PUT')
        @include('admin.forms.form', ['submitLabel' => __('Update')])
    </form>
@endsection

