<?php

namespace App\Http\Controllers;

use App\Support\DiscordAvatar;
use App\Support\OAuthProviders;
use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request): View
    {
        $user = $request->user()->load('socialAccounts');

        return view('account.dashboard', [
            'user' => $user,
            'oauthGoogle' => OAuthProviders::isConfigured('google'),
            'oauthDiscord' => OAuthProviders::isConfigured('discord'),
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if (! Schema::hasColumn('users', 'avatar')) {
            return redirect()->route('account.dashboard')->with('error', __('Profile picture could not be updated.'));
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => $request->file('avatar')->store('avatars', 'public'),
        ]);

        return redirect()->route('account.dashboard')->with('success', __('Profile picture updated.'));
    }

    public function avatarFromDiscord(Request $request): RedirectResponse
    {
        $user = $request->user();
        $account = $user->socialAccounts()->where('provider', 'discord')->first();

        if (! $account) {
            return redirect()->route('account.dashboard')->with('error', __('Link Discord first to use its profile picture.'));
        }

        if (! Schema::hasColumn('users', 'avatar')) {
            return redirect()->route('account.dashboard')->with('error', __('Profile picture could not be updated.'));
        }

        $url = DiscordAvatar::cdnUrl($account->provider_id, $account->provider_avatar);

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => config('app.name', 'Laravel').'/1.0'])
                ->get($url);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('account.dashboard')->with('error', __('Could not load Discord profile picture.'));
        }

        if (! $response->successful()) {
            return redirect()->route('account.dashboard')->with('error', __('Could not load Discord profile picture.'));
        }

        $body = $response->body();
        if (strlen($body) > 3 * 1024 * 1024) {
            return redirect()->route('account.dashboard')->with('error', __('Could not load Discord profile picture.'));
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($body);
        $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        if (! in_array($mime, $allowed, true)) {
            return redirect()->route('account.dashboard')->with('error', __('Could not load Discord profile picture.'));
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'png',
        };

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = 'avatars/'.Str::random(40).'.'.$ext;
        Storage::disk('public')->put($path, $body);
        $user->update(['avatar' => $path]);

        return redirect()->route('account.dashboard')->with('success', __('Profile picture updated from Discord.'));
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'current_password' => ['required', 'current_password'],
        ]);

        $request->user()->update([
            'email' => $request->validated('email'),
        ]);

        return redirect()->route('account.dashboard')->with('success', __('Email address updated.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', PasswordRules::default()],
        ]);

        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()->route('account.dashboard')->with('success', __('Password updated.'));
    }

    public function unlinkOAuth(Request $request, string $provider): RedirectResponse
    {
        if (! OAuthProviders::isSupported($provider)) {
            abort(404);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $deleted = $user->socialAccounts()->where('provider', $provider)->delete();

        if ($deleted === 0) {
            return redirect()->route('account.dashboard')->with('error', __('No linked :provider account found.', ['provider' => match ($provider) {
                'google' => 'Google',
                'discord' => 'Discord',
                default => $provider,
            }]));
        }

        return redirect()->route('account.dashboard')->with('success', __(':provider has been unlinked.', ['provider' => match ($provider) {
            'google' => 'Google',
            'discord' => 'Discord',
            default => $provider,
        }]));
    }
}
