<section class="hw-auth-page">
    <div class="hw-auth-page__header text-center mb-8">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-6" aria-label="{{ __('Home') }}">
            <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25">
                <i class="fas fa-snowflake text-white text-xl" aria-hidden="true"></i>
            </div>
        </a>
        <h2 class="hw-auth-page__title">{{ $authTitle ?? '' }}</h2>
        @if(filled($authSubtitle ?? null))
            <p class="hw-auth-page__lead">{{ $authSubtitle }}</p>
        @endif
    </div>
    <div class="hw-auth-card">
