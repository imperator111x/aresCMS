@php
    $isReply = $isReply ?? false;
@endphp
<div class="{{ $isReply ? 'ml-4 md:ml-8 mt-4 pl-4 border-l-2 border-primary-200 dark:border-primary-900/40' : 'bg-white dark:bg-dark-800 rounded-2xl border border-gray-200 dark:border-dark-700 p-6' }}">
    <div class="flex items-center justify-between mb-3 gap-2">
        <div class="flex items-center gap-3 min-w-0">
            @if($comment->user->avatar)
                <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-dark-600 shrink-0">
            @else
                <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-purple-400 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-white">{{ substr($comment->user->name, 0, 1) }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $comment->user->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $comment->formatted_date }}</p>
            </div>
        </div>
        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->is_admin))
            <form action="{{ route('news.comments.destroy', [$news, $comment]) }}" method="POST" class="shrink-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="{{ __('Delete') }}" onclick="return confirm('{{ __('Are you sure you want to delete this comment?') }}')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->content }}</p>

    @if(!$isReply)
        @auth
            @if(!auth()->user()->is_banned)
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-medium text-primary-500 hover:text-primary-600 select-none">
                        <i class="fas fa-reply mr-1"></i>{{ __('Reply') }}
                    </summary>
                    <form action="{{ route('news.comments.store', $news) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <textarea name="content" rows="3" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-dark-700 border border-gray-300 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-400 text-sm"
                            placeholder="{{ __('Write your reply...') }}"></textarea>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl transition-colors">
                            <i class="fas fa-paper-plane"></i>
                            {{ __('Post Reply') }}
                        </button>
                    </form>
                </details>
            @endif
        @endauth

        @foreach($comment->replies as $reply)
            @include('news.partials.comment-node', ['news' => $news, 'comment' => $reply, 'isReply' => true])
        @endforeach
    @endif
</div>
