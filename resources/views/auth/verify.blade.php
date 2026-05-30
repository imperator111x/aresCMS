@extends('layouts.app')

@section('content')
    <div class="card" style="max-width: 400px; margin: 0 auto;">
        <h1 style="color: var(--md-primary); margin-bottom: 1.5rem; text-align: center;">
            <span class="material-icons">mark_email_read</span>
            {{ __('Verify Email') }}
        </h1>

        @if(session('resent'))
            <div class="alert alert-success">
                {{ __('A fresh verification link has been sent to your email address') }}.
            </div>
        @endif

        <p style="margin-bottom: 1rem;">{{ __('Before proceeding, please check your email for a verification link') }}.</p>
        <p style="margin-bottom: 1rem;">{{ __('If you did not receive the email') }},</p>

        <form method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <span class="material-icons">refresh</span>
                {{ __('Resend Verification Email') }}
            </button>
        </form>
    </div>
@endsection
