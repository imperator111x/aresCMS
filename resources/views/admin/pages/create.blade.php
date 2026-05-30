@extends('layouts.admin')

@section('title', __('Create Page'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Create Page') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Build your page by adding blocks and reordering them.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.pages.form', ['submitLabel' => __('Create')])
    </form>
@endsection

