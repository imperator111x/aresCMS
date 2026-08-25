<article class="group hw-news-card">
    @if($article->image)
        <a href="{{ route('news.show', $article) }}" class="hw-news-card__media">
            <img src="{{ asset('storage/'.$article->image) }}" alt="{{ $article->title }}" loading="lazy" decoding="async">
        </a>
    @else
        <a href="{{ route('news.show', $article) }}" class="hw-news-card__media hw-news-card__media--placeholder">
            <i class="fas fa-fan" aria-hidden="true"></i>
        </a>
    @endif
    <div class="hw-news-card__body">
        <div class="hw-news-card__meta">
            @if($article->category)
                <span class="hw-news-card__category">{{ $article->category }}</span>
            @endif
            <span><i class="fas fa-calendar" aria-hidden="true"></i> {{ $article->formatted_date }}</span>
        </div>
        <h3 class="hw-news-card__title">
            <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
        </h3>
        <p class="hw-news-card__excerpt">{{ $article->excerpt }}</p>
        <a href="{{ route('news.show', $article) }}" class="hw-news-card__more">
            {{ __('Read More') }}
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
