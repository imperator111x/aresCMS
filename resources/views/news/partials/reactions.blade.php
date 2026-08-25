@php
    use App\Support\ReactionType;

    $reactionCounts = $reactionCounts ?? [];
    $userReaction = $userReaction ?? null;
    $totalReactions = array_sum($reactionCounts);
@endphp

<section class="mt-8" aria-label="{{ __('Reactions') }}">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        <i class="fas fa-heart mr-2 text-primary-500"></i>
        {{ __('Reactions') }}
        @if($totalReactions > 0)
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $totalReactions }})</span>
        @endif
    </h2>

    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-4 md:p-6">
        @auth
            @if(auth()->user()->is_banned)
                <p class="text-sm text-amber-700 dark:text-amber-300 flex items-center gap-2 mb-4">
                    <i class="fas fa-ban"></i>
                    {{ __('Your account cannot react to articles.') }}
                </p>
            @endif

            <div class="flex flex-wrap gap-2 {{ auth()->user()->is_banned ? 'opacity-50 pointer-events-none' : '' }}">
                @foreach(ReactionType::definitions() as $type => $meta)
                    @php
                        $count = (int) ($reactionCounts[$type] ?? 0);
                        $isActive = $userReaction === $type;
                    @endphp
                    <form action="{{ route('news.reactions.toggle', $news) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <button
                            type="submit"
                            title="{{ __($meta['label']) }}"
                            @class([
                                'inline-flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-medium transition-all',
                                'bg-primary-500 border-primary-500 text-white shadow-lg shadow-primary-500/25' => $isActive,
                                'bg-gray-50 dark:bg-dark-700 border-gray-200 dark:border-dark-600 text-gray-700 dark:text-gray-200 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400' => ! $isActive,
                            ])
                        >
                            <i class="fas {{ $meta['icon'] }}"></i>
                            <span>{{ __($meta['label']) }}</span>
                            @if($count > 0)
                                <span @class([
                                    'inline-flex min-w-[1.25rem] justify-center rounded-full px-1.5 py-0.5 text-xs font-bold',
                                    'bg-white/20 text-white' => $isActive,
                                    'bg-gray-200 dark:bg-dark-600 text-gray-700 dark:text-gray-200' => ! $isActive,
                                ])>{{ $count }}</span>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Please login to react to this article.') }}</p>
            <div class="flex flex-wrap gap-2 opacity-90">
                @foreach(ReactionType::definitions() as $type => $meta)
                    @php $count = (int) ($reactionCounts[$type] ?? 0); @endphp
                    @if($count > 0)
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 dark:border-dark-600 bg-gray-50 dark:bg-dark-700 text-sm text-gray-700 dark:text-gray-200">
                            <i class="fas {{ $meta['icon'] }}"></i>
                            <span>{{ __($meta['label']) }}</span>
                            <span class="text-xs font-bold bg-gray-200 dark:bg-dark-600 rounded-full px-2 py-0.5">{{ $count }}</span>
                        </span>
                    @endif
                @endforeach
            </div>
            <a href="{{ route('login') }}" class="mt-4 inline-flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                <i class="fas fa-sign-in-alt"></i>
                {{ __('Login') }}
            </a>
        @endauth
    </div>
</section>
