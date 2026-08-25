    </div>
    @if($authBack ?? true)
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="hw-auth-back">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                {{ __('Back to Home') }}
            </a>
        </div>
    @endif
</section>
