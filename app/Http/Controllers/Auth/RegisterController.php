<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\ActivityLogger;
use App\Support\OAuthProviders;
use App\Support\PasswordRules;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation.
    |
    */

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        if (Setting::getBoolValue('disable_registration', false)) {
            return redirect()->route('login')->with('error', __('Registration is currently disabled.'));
        }

        return view('auth.register', [
            'turnstileSiteKey' => config('services.cloudflare.turnstile.site_key'),
            'oauthGoogle' => OAuthProviders::isConfigured('google'),
            'oauthDiscord' => OAuthProviders::isConfigured('discord'),
            'passwordPolicy' => PasswordRules::policySummary(),
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        if (Setting::getBoolValue('disable_registration', false)) {
            return redirect()->route('login')->with('error', __('Registration is currently disabled.'));
        }

        $this->validator($request->all())->validate();

        // Verify Cloudflare Turnstile CAPTCHA
        if (!$this->verifyTurnstile($request)) {
            return back()->withErrors([
                'captcha' => __('CAPTCHA verification failed. Please try again.'),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        ActivityLogger::log('user.registered', $user->email, $user, ['name' => $user->name]);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? response()->json(['message' => 'Registration successful'])
                    : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', PasswordRules::default()],
        ];

        // Only require CAPTCHA if secret key is configured
        if (!empty(config('services.cloudflare.turnstile.secret_key'))) {
            $rules['cf-turnstile-response'] = ['required', 'string'];
        }

        return Validator::make($data, $rules);
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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the post-registration redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return $this->redirectTo;
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
