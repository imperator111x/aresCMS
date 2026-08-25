<section class="relative overflow-hidden hw-hero-panel">
    <div class="absolute inset-0 hw-hero-panel__glow"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="hw-hero-badge">
            <i class="fas fa-scale-balanced" aria-hidden="true"></i>
            <span>{{ __('Legal information') }}</span>
        </div>
        <h1 class="handwerk-brand hw-hero-panel__title">{{ $docTitle ?? '' }}</h1>
        @if(filled($docSubtitle ?? null))
            <p class="hw-hero-panel__lead">{{ $docSubtitle }}</p>
        @endif
    </div>
</section>
<section class="py-10 md:py-14">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hw-legal-panel hw-legal-prose">
