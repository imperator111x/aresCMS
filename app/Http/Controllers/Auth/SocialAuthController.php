<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Setting;
use App\Models\User;
use App\Support\OAuthProviders;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectLogin(Request $request, string $provider): RedirectResponse
    {
        return $this->startGuestRedirect($request, $provider, 'login');
    }

    public function redirectRegister(Request $request, string $provider): RedirectResponse
    {
        if (Setting::getBoolValue('disable_registration', false)) {
            return redirect()->route('login')->with('error', __('Registration is currently disabled.'));
        }

        return $this->startGuestRedirect($request, $provider, 'register');
    }

    protected function startGuestRedirect(Request $request, string $provider, string $intent): RedirectResponse
    {
        if (! OAuthProviders::isSupported($provider) || ! OAuthProviders::isConfigured($provider)) {
            abort(404);
        }

        if (! in_array($intent, ['login', 'register'], true)) {
            abort(404);
        }

        $request->session()->put('oauth.intent', $intent);

        return $this->socialiteRedirect($provider);
    }

    public function redirectLink(Request $request, string $provider): RedirectResponse
    {
        if (! OAuthProviders::isSupported($provider) || ! OAuthProviders::isConfigured($provider)) {
            abort(404);
        }

        $request->session()->put('oauth.intent', 'link');

        return $this->socialiteRedirect($provider);
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! OAuthProviders::isSupported($provider) || ! OAuthProviders::isConfigured($provider)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->failureRedirect($request, __('OAuth sign-in failed. Please try again.'));
        }

        $providerId = (string) $socialUser->getId();
        $email = $socialUser->getEmail();
        if (! filled($email)) {
            return $this->failureRedirect(
                $request,
                __('Your :provider account has no email on file. Please use another sign-in method or add an email in :provider.', ['provider' => $this->providerLabel($provider)])
            );
        }

        $email = Str::lower($email);

        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if (Auth::check()) {
            return $this->handleLinkCallback($request, $provider, $providerId, $account);
        }

        /** @var string $intent */
        $intent = (string) $request->session()->pull('oauth.intent', 'login');

        if ($account) {
            $user = $account->user;
            if ($user->is_banned) {
                return $this->failureRedirect($request, __('Your account has been suspended.'));
            }

            $this->syncProviderAvatarFromSocialite($provider, $account, $socialUser);

            return $this->finalizeLogin($request, $user);
        }

        $userByEmail = User::query()->where('email', $email)->first();
        if ($userByEmail) {
            if ($userByEmail->is_banned) {
                return $this->failureRedirect($request, __('Your account has been suspended.'));
            }

            $linked = SocialAccount::query()->firstOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $providerId,
                ],
                [
                    'user_id' => $userByEmail->id,
                    'provider_avatar' => $this->providerAvatarFromSocialite($provider, $socialUser),
                ]
            );
            $this->syncProviderAvatarFromSocialite($provider, $linked, $socialUser);

            return $this->finalizeLogin($request, $userByEmail);
        }

        if ($intent !== 'register') {
            return $this->failureRedirect(
                $request,
                __('No account found for this sign-in. Register first or use email and password.')
            );
        }

        if (Setting::getBoolValue('disable_registration', false)) {
            return $this->failureRedirect($request, __('Registration is currently disabled.'));
        }

        $name = $socialUser->getName()
            ?: $socialUser->getNickname()
            ?: Str::before($email, '@');

        $user = User::query()->create([
            'name' => Str::limit($name, 255),
            'email' => $email,
            'password' => Hash::make(Str::random(48)),
            'email_verified_at' => now(),
        ]);

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerId,
        ]);

        event(new Registered($user));

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended('/');
    }

    protected function handleLinkCallback(Request $request, string $provider, string $providerId, ?SocialAccount $account): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($account) {
            if ((int) $account->user_id === (int) $user->id) {
                return redirect()->route('account.dashboard')->with('success', __('This account is already linked.'));
            }

            return redirect()->route('account.dashboard')->with('error', __('This :provider account is already linked to another user.', ['provider' => $this->providerLabel($provider)]));
        }

        if ($user->socialAccounts()->where('provider', $provider)->exists()) {
            return redirect()->route('account.dashboard')->with('error', __('You already have a :provider account linked.', ['provider' => $this->providerLabel($provider)]));
        }

        SocialAccount::query()->create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerId,
            'provider_avatar' => $this->providerAvatarFromSocialite($provider, $socialUser),
        ]);

        return redirect()->route('account.dashboard')->with('success', __('Account linked successfully.'));
    }

    protected function finalizeLogin(Request $request, User $user): RedirectResponse
    {
        if ($user->is_banned) {
            return $this->failureRedirect($request, __('Your account has been suspended.'));
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('two_factor.pending_user_id', $user->id);
            $request->session()->put('two_factor.remember', true);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended('/');
    }

    protected function failureRedirect(Request $request, string $message): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard')->with('error', $message);
        }

        return redirect()->route('login')->with('error', $message);
    }

    protected function socialiteRedirect(string $provider): RedirectResponse
    {
        $driver = Socialite::driver($provider);
        $intent = session('oauth.intent');

        if ($provider === 'discord' && in_array($intent, ['register', 'link'], true)) {
            $driver = $driver->withConsent();
        }

        return $driver->redirect();
    }

    protected function providerLabel(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'discord' => 'Discord',
            default => $provider,
        };
    }

    /**
     * @param  \Laravel\Socialite\Contracts\User  $socialUser
     */
    protected function providerAvatarFromSocialite(string $provider, $socialUser): ?string
    {
        if ($provider !== 'discord') {
            return null;
        }

        $raw = $socialUser->getRaw();

        return isset($raw['avatar']) ? (string) $raw['avatar'] : null;
    }

    /**
     * @param  \Laravel\Socialite\Contracts\User  $socialUser
     */
    protected function syncProviderAvatarFromSocialite(string $provider, SocialAccount $account, $socialUser): void
    {
        if ($provider !== 'discord') {
            return;
        }

        $hash = $this->providerAvatarFromSocialite($provider, $socialUser);
        if ($account->provider_avatar !== $hash) {
            $account->update(['provider_avatar' => $hash]);
        }
    }
}
