<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.settings.general') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.general*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
        <i class="fas fa-sliders-h"></i>
        {{ __('General Settings') }}
    </a>
    <a href="{{ route('admin.settings.themes') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.themes*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
        <i class="fas fa-palette"></i>
        {{ __('Themes') }}
    </a>
    <a href="{{ route('admin.settings.languages') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.languages*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
        <i class="fas fa-language"></i>
        {{ __('Language Settings') }}
    </a>
    <a href="{{ route('admin.settings.legal-imprint') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.legal-imprint*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
        <i class="fas fa-balance-scale"></i>
        {{ __('Legal notice (Imprint)') }}
    </a>
    <a href="{{ route('admin.settings.cookie-consent') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border {{ request()->routeIs('admin.settings.cookie-consent*') ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-700' }}">
        <i class="fas fa-cookie-bite"></i>
        {{ __('Cookie consent') }}
    </a>
</div>
