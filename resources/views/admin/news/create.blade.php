@extends('layouts.admin')

@section('title', __('Create News'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Create News') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Create a new news article') }}</p>
        </div>
        <a href="{{ route('admin.news.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
            {{ __('Back to List') }}
        </a>
    </div>
    
    <!-- Form -->
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Title') }} *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Category') }}</label>
                        <select name="category" id="category" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white @error('category') border-red-500 @enderror">
                            <option value="">{{ __('No category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Content (rich text editor) -->
                    <div>
                        <label for="news-content-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Content') }} *</label>
                        @include('admin.news.partials.rich-text-editor', ['initialHtml' => old('content', '')])
                        @error('content')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Image -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Featured Image') }}</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-dark-600 border-dashed rounded-lg hover:border-primary-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="image" class="relative cursor-pointer rounded-md font-medium text-primary-500 hover:text-primary-600">
                                        <span>{{ __('Upload a file') }}</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">{{ __('or drag and drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">{{ __('PNG, JPG, GIF up to 2MB') }}</p>
                            </div>
                        </div>
                        @error('image')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }}</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                                <input type="radio" name="published" value="1" {{ old('published', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-primary-500 focus:ring-primary-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Published') }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Article will be visible to everyone') }}</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                                <input type="radio" name="published" value="0" {{ old('published') == '0' ? 'checked' : '' }} class="w-4 h-4 text-primary-500 focus:ring-primary-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Draft') }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Article will be saved as draft') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Publish date / schedule') }}</label>
                        <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white @error('published_at') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave empty to publish immediately when status is Published. Future date = scheduled.') }}</p>
                        @error('published_at')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                            <input type="checkbox" name="comments_enabled" value="1" @checked(old('comments_enabled', '1') === '1') class="mt-0.5 w-4 h-4 text-primary-500 focus:ring-primary-500 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Allow comments') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Visitors can comment on this article.') }}</p>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Submit -->
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-save"></i>
                            {{ __('Create News') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
