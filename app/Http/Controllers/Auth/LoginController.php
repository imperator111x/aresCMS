<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use App\Support\OAuthProviders;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'turnstileSiteKey' => config('services.cloudflare.turnstile.site_key'),
            'oauthGoogle' => OAuthProviders::isConfigured('google'),
            'oauthDiscord' => OAuthProviders::isConfigured('discord'),
        ]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (! $this->verifyTurnstile($request)) {
            return back()->withErrors([
                'captcha' => __('CAPTCHA verification failed. Please try again.'),
            ])->withInput($request->except('password'));
        }

        $user = $this->retrieveAuthenticatedUser($request);
        if (! $user) {
            return $this->sendFailedLoginResponse($request);
        }

        if ($user->is_banned) {
            LoginHistory::recordFailure($request, $user->email, 'banned', $user->id);
            throw ValidationException::withMessages([
                $this->username() => [__('Your account has been suspended.')],
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('two_factor.pending_user_id', $user->id);
            $request->session()->put('two_factor.remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($request->hasSession()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        if ($response = $this->authenticated($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json(['message' => 'Login successful'])
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Benutzer anhand E-Mail/Name + Passwort ermitteln (ohne Session).
     */
    protected function retrieveAuthenticatedUser(Request $request): ?User
    {
        $credentials = $this->credentials($request);
        $field = array_key_first($credentials);
        if ($field === 'password') {
            return null;
        }

        $user = User::query()->where($field, $credentials[$field])->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        // Only require CAPTCHA if secret key is configured
        if (!empty(config('services.cloudflare.turnstile.secret_key'))) {
            $rules['cf-turnstile-response'] = 'required|string';
        }

        $request->validate($rules);
    }

    /**
     * Verify Cloudflare Turnstile CAPTCHA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifyTurnstile(Request $request): bool
    {
        $secretKey = config('services.cloudflare.turnstile.secret_key');
        
        if (empty($secretKey)) {
            // If no secret key is configured, skip verification
            return true;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        return $result['success'] ?? false;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $login = $request->input($this->username());
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        LoginHistory::recordFailure(
            $request,
            (string) $request->input($this->username(), ''),
            'invalid_credentials'
        );

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'login';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json(['message' => 'Logged out'])
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the post-login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return $this->redirectTo;
    }
}
