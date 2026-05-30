@extends('layouts.admin')

@section('title', __('Edit User'))

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Edit User') }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('Edit user details') }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
            {{ __('Back to List') }}
        </a>
    </div>
    
    <!-- Form -->
    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Name') }} *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }} *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Task -->
                    <div>
                        <label for="task" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Task / Position') }}</label>
                        <input type="text" name="task" id="task" value="{{ old('task', $user->task) }}" placeholder="{{ __('e.g., Developer, Designer, Manager') }}"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('task') border-red-500 @enderror">
                        @error('task')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Bio') }}</label>
                        <textarea name="bio" id="bio" rows="4" placeholder="{{ __('Tell us about yourself...') }}"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 @error('bio') border-red-500 @enderror">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Current Avatar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Current Avatar') }}</label>
                        <div class="flex justify-center">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF&size=128" alt="{{ $user->name }}" class="w-32 h-32 rounded-full">
                            @endif
                        </div>
                    </div>
                    
                    <!-- Avatar Upload -->
                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Upload New Avatar') }}</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-dark-600 border-dashed rounded-lg hover:border-primary-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="avatar" class="relative cursor-pointer rounded-md font-medium text-primary-500 hover:text-primary-600">
                                        <span>{{ __('Upload a file') }}</span>
                                        <input id="avatar" name="avatar" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">{{ __('or drag and drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">{{ __('PNG, JPG, GIF, WebP up to 2MB') }}</p>
                            </div>
                        </div>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Role / Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Role') }}</label>
                        @if($canManageRoles && $user->id !== auth()->id())
                            @php
                                $selectedRole = old('role', $user->role ?: ($user->is_admin ? 'admin' : 'moderator'));
                                $selectedPermissions = old('admin_permissions', $user->normalizedAdminPermissions() ?? []);
                            @endphp
                            <select name="role" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white">
                                <option value="owner" {{ $selectedRole === 'owner' ? 'selected' : '' }}>{{ __('Owner') }}</option>
                                <option value="admin" {{ $selectedRole === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                                <option value="moderator" {{ $selectedRole === 'moderator' ? 'selected' : '' }}>{{ __('Moderator') }}</option>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('Owner can assign roles and permissions for each admin page.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Only owner has full access automatically. Admin and Moderator use the selected page permissions.') }}</p>

                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Admin page permissions') }}</p>
                                <div class="space-y-2 max-h-64 overflow-auto pr-1">
                                    @foreach($permissionOptions as $permissionKey => $permissionLabel)
                                        <label class="flex items-center gap-3 p-2.5 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                                            <input type="checkbox" name="admin_permissions[]" value="{{ $permissionKey }}" {{ in_array($permissionKey, $selectedPermissions, true) ? 'checked' : '' }} class="w-4 h-4 text-primary-500 focus:ring-primary-500 rounded">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ __($permissionLabel) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-3 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __($user->adminRoleLabel()) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Only owner can change roles and page permissions.') }}</p>
                            </div>
                            @if($user->id !== auth()->id() && ! $user->isOwner())
                                <label class="mt-3 flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="w-4 h-4 text-primary-500 focus:ring-primary-500 rounded">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Admin') }}</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('User will have admin privileges') }}</p>
                                    </div>
                                </label>
                            @endif
                        @endif
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }}</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <input type="checkbox" name="is_banned" value="1" {{ old('is_banned', $user->is_banned) ? 'checked' : '' }} {{ $user->id === auth()->id() ? 'disabled' : '' }} class="w-4 h-4 text-red-500 focus:ring-red-500 rounded">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Banned') }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Banned users cannot post comments and are excluded from the public team page.') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-save"></i>
                            {{ __('Update User') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
