<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsReactionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MaintenanceAdminLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\LoginHistoryController as AdminLoginHistoryController;
use App\Http\Controllers\Admin\NewsCategoryController as AdminNewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OperationsController as AdminOperationsController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PluginController as AdminPluginController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\SystemUpdateController as AdminSystemUpdateController;
use App\Http\Controllers\Admin\ThemeController as AdminThemeController;
use App\Http\Controllers\Admin\TwoFactorSecurityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RedirectController as AdminRedirectController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\LicenseActivationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicStorageController;
use App\Http\Middleware\EnsureValidLicense;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
| Fallback, wenn public/storage → storage/app/public nicht per Symlink erreichbar ist (z. B. Webspace).
| Symlink hat Vorrang: liefert der Webserver die Datei direkt aus, greift diese Route nicht.
*/
Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.public');

Route::withoutMiddleware([EnsureValidLicense::class])->group(function () {
    Route::get('/license', [LicenseActivationController::class, 'show'])->name('license.show');
    Route::post('/license', [LicenseActivationController::class, 'store'])
        ->middleware('throttle:license-activate')
        ->name('license.store');
});

Route::get('/', [NewsController::class, 'home'])->name('home');
Route::get('/news', [NewsController::class, 'allNews'])->name('news.index');

Route::get('/impressum', [LegalController::class, 'imprint'])->name('legal.imprint');
Route::get('/datenschutz', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/agb', [LegalController::class, 'terms'])->name('legal.terms');

// Public Team List (nur Admins, nicht gesperrt)
Route::get('/team', function () {
    $users = \App\Models\User::query()
        ->when(
            Schema::hasColumn('users', 'role'),
            fn ($query) => $query->where(function ($inner) {
                $inner->whereIn('role', [
                    \App\Models\User::ROLE_OWNER,
                    \App\Models\User::ROLE_ADMIN,
                    \App\Models\User::ROLE_MODERATOR,
                ])->orWhere('is_admin', true);
            }),
            fn ($query) => $query->where('is_admin', true)
        )
        ->where('is_banned', false)
        ->when(
            Schema::hasColumn('users', 'team_visible'),
            fn ($query) => $query->where('team_visible', true)
        )
        ->with(['socialAccounts' => function ($query) {
            $query->where('provider', 'discord')
                ->select('id', 'user_id', 'provider', 'provider_id');
        }])
        ->when(
            Schema::hasColumn('users', 'team_sort_order'),
            fn ($query) => $query->orderByRaw('CASE WHEN team_sort_order IS NULL THEN 1 ELSE 0 END')->orderBy('team_sort_order')
        )
        ->orderBy('created_at', 'asc')
        ->get();

    return view('team', compact('users'));
})->name('team');

// Language switcher
Route::get('/language/{locale}', function ($locale) {
    $locale = str_replace('_', '-', trim((string) $locale));
    $isFormatValid = (bool) preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $locale);
    $exists = file_exists(resource_path('lang/'.$locale.'.json'));
    if ($isFormatValid && $exists) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');

// Admin-Login nur sinnvoll während Wartungsmodus (Route bleibt erreichbar; außerhalb → Redirect auf normales Login)
Route::get('/wartung/admin-anmeldung', [MaintenanceAdminLoginController::class, 'create'])->name('maintenance.admin.login');
Route::post('/wartung/admin-anmeldung', [MaintenanceAdminLoginController::class, 'store'])->middleware('throttle:login')->name('maintenance.admin.login.store');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::get('/two-factor/challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
Route::post('/two-factor/challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:two-factor')->name('two-factor.verify');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes
Route::middleware('registration.enabled')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:register');
});

// Password Reset Routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('throttle:password-reset')
    ->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
    ->middleware('throttle:password-reset')
    ->name('password.update');

// Email Verification Routes
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// OAuth (Google / Discord)
Route::middleware(['guest', 'throttle:oauth'])->group(function () {
    Route::get('/oauth/{provider}/redirect/login', [SocialAuthController::class, 'redirectLogin'])
        ->whereIn('provider', \App\Support\OAuthProviders::SUPPORTED)
        ->name('oauth.redirect.login');
    Route::get('/oauth/{provider}/redirect/register', [SocialAuthController::class, 'redirectRegister'])
        ->middleware('registration.enabled')
        ->whereIn('provider', \App\Support\OAuthProviders::SUPPORTED)
        ->name('oauth.redirect.register');
});
Route::get('/oauth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->middleware('throttle:oauth')
    ->whereIn('provider', \App\Support\OAuthProviders::SUPPORTED)
    ->name('oauth.callback');

Route::middleware(['auth', 'throttle:oauth'])->group(function () {
    Route::get('/oauth/{provider}/link', [SocialAuthController::class, 'redirectLink'])
        ->whereIn('provider', \App\Support\OAuthProviders::SUPPORTED)
        ->name('oauth.redirect.link');
});

Route::middleware(['auth', 'throttle:account'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'show'])->name('dashboard');
    Route::post('/avatar', [AccountController::class, 'updateAvatar'])->name('avatar.update');
    Route::post('/avatar/discord', [AccountController::class, 'avatarFromDiscord'])->name('avatar.discord');
    Route::patch('/email', [AccountController::class, 'updateEmail'])->name('email.update');
    Route::patch('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::delete('/oauth/{provider}', [AccountController::class, 'unlinkOAuth'])
        ->whereIn('provider', \App\Support\OAuthProviders::SUPPORTED)
        ->name('oauth.unlink');
});

// News Routes
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::post('/forms/{slug}/submit', [FormSubmissionController::class, 'store'])
    ->middleware('throttle:forms')
    ->name('forms.submit');
Route::post('/news/{news}/reactions', [NewsReactionController::class, 'toggle'])
    ->name('news.reactions.toggle')
    ->middleware(['auth', 'throttle:reactions']);
Route::post('/news/{news}/comments', [NewsController::class, 'storeComment'])->name('news.comments.store')->middleware(['auth', 'throttle:comments']);
Route::delete('/news/{news}/comments/{comment}', [NewsController::class, 'destroyComment'])->name('news.comments.destroy')->middleware(['auth', 'throttle:comments']);

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/redirects', [AdminRedirectController::class, 'index'])->name('redirects.index');
    Route::post('/redirects', [AdminRedirectController::class, 'store'])->name('redirects.store');
    Route::put('/redirects/{redirect}', [AdminRedirectController::class, 'update'])->name('redirects.update');
    Route::delete('/redirects/{redirect}', [AdminRedirectController::class, 'destroy'])->name('redirects.destroy');

    Route::get('/search/suggestions', [AdminSearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search', [AdminSearchController::class, 'index'])->name('search');

    Route::get('/notifications/feed', [AdminNotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/clear-history', [AdminNotificationController::class, 'clearHistory'])->name('notifications.clear-history');

    Route::get('/activity-log', [AdminActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/export/pdf', [AdminActivityLogController::class, 'exportPdf'])->name('activity-log.export.pdf');
    Route::get('/activity-log/export/excel', [AdminActivityLogController::class, 'exportExcel'])->name('activity-log.export.excel');

    Route::get('/operations', [AdminOperationsController::class, 'index'])->name('operations.index');
    Route::get('/operations/schedule', [AdminOperationsController::class, 'schedule'])->name('operations.schedule');
    Route::put('/operations/schedule', [AdminOperationsController::class, 'updateSchedule'])->name('operations.schedule.update');
    Route::match(['post', 'put'], '/operations/schedule/{job}/run', [AdminOperationsController::class, 'runScheduledJob'])->name('operations.schedule.run');
    Route::get('/operations/cli-console', [AdminOperationsController::class, 'cliConsole'])->name('operations.cli');
    Route::post('/operations/cli-console', [AdminOperationsController::class, 'runCliCommand'])
        ->middleware('throttle:10,1')
        ->name('operations.cli.execute');
    Route::post('/operations/backup', [AdminOperationsController::class, 'runBackup'])
        ->middleware('throttle:6,60')
        ->name('operations.backup');
    Route::post('/operations/backup/restore', [AdminOperationsController::class, 'restoreBackup'])
        ->middleware('throttle:3,60')
        ->name('operations.backup.restore');
    Route::post('/operations/migrate', [AdminOperationsController::class, 'runMigrate'])
        ->middleware('throttle:3,60')
        ->name('operations.migrate');
    Route::post('/operations/cache-clear', [AdminOperationsController::class, 'runCacheClear'])
        ->middleware('throttle:60,60')
        ->name('operations.cache-clear');
    Route::post('/operations/frontend-build', [AdminOperationsController::class, 'runFrontendBuild'])
        ->middleware('throttle:2,30')
        ->name('operations.frontend-build');
    Route::post('/operations/dependencies/update', [AdminOperationsController::class, 'updateDependencies'])
        ->middleware('throttle:operations-dependencies')
        ->name('operations.dependencies.update');
    Route::post('/operations/maintenance/enable', [AdminOperationsController::class, 'maintenanceEnable'])
        ->middleware('throttle:12,60')
        ->name('operations.maintenance.enable');
    Route::post('/operations/maintenance/disable', [AdminOperationsController::class, 'maintenanceDisable'])
        ->middleware('throttle:12,60')
        ->name('operations.maintenance.disable');
    Route::get('/operations/server-info', [AdminOperationsController::class, 'serverInfo'])->name('operations.server-info');
    Route::get('/operations/health-check', [AdminOperationsController::class, 'healthCheck'])->name('operations.health-check');
    Route::get('/operations/report/pdf', [AdminOperationsController::class, 'exportSystemReportPdf'])->name('operations.report.pdf');

    Route::get('/system-update', [AdminSystemUpdateController::class, 'index'])->name('system-update.index');
    Route::post('/system-update/apply', [AdminSystemUpdateController::class, 'apply'])
        ->middleware('throttle:3,120')
        ->name('system-update.apply');

    Route::get('/login-history', [AdminLoginHistoryController::class, 'index'])->name('login-history.index');
    Route::get('/login-history/export/pdf', [AdminLoginHistoryController::class, 'exportPdf'])->name('login-history.export.pdf');
    Route::get('/login-history/export/excel', [AdminLoginHistoryController::class, 'exportExcel'])->name('login-history.export.excel');

    Route::get('/security/two-factor', [TwoFactorSecurityController::class, 'show'])->name('security.two-factor');
    Route::post('/security/two-factor/begin', [TwoFactorSecurityController::class, 'begin'])->name('security.two-factor.begin');
    Route::post('/security/two-factor/confirm', [TwoFactorSecurityController::class, 'confirm'])->name('security.two-factor.confirm');
    Route::post('/security/two-factor/disable', [TwoFactorSecurityController::class, 'disable'])->name('security.two-factor.disable');
    Route::get('/plugins', [AdminPluginController::class, 'index'])->name('plugins.index');
    Route::post('/plugins/upload', [AdminPluginController::class, 'upload'])->name('plugins.upload');
    Route::post('/plugins/{directory}/toggle', [AdminPluginController::class, 'toggle'])
        ->where('directory', '[A-Za-z0-9._-]+')
        ->name('plugins.toggle');

    // News Management
    Route::resource('news', AdminNewsController::class);
    Route::get('news-categories', [AdminNewsCategoryController::class, 'index'])->name('news-categories.index');
    Route::post('news-categories', [AdminNewsCategoryController::class, 'store'])->name('news-categories.store');
    Route::put('news-categories/{category}', [AdminNewsCategoryController::class, 'update'])->name('news-categories.update');
    Route::delete('news-categories/{category}', [AdminNewsCategoryController::class, 'destroy'])->name('news-categories.destroy');
    Route::resource('pages', AdminPageController::class)->except(['show']);
    Route::post('pages/inline-image-upload', [AdminPageController::class, 'uploadInlineImage'])->name('pages.inline-image.upload');
    Route::resource('forms', AdminFormController::class)->except(['show']);
    Route::get('forms/{form}/submissions', [AdminFormController::class, 'submissions'])->name('forms.submissions');
    Route::delete('forms/{form}/submissions', [AdminFormController::class, 'clearSubmissions'])->name('forms.submissions.clear');
    Route::post('pages/{page}/revisions/{revision}/restore', [AdminPageController::class, 'restoreRevision'])->name('pages.revisions.restore');

    // User Management
    Route::resource('users', AdminUserController::class);
    Route::patch('users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::patch('users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('users.toggle-ban');
    
    // Team List
    Route::get('/team', [AdminUserController::class, 'team'])->name('team');
    Route::post('/team/{user}/banner', [AdminUserController::class, 'updateTeamBanner'])->name('team.banner.update');
    
    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/general', [App\Http\Controllers\Admin\SettingController::class, 'general'])->name('settings.general');
    Route::put('/settings/general', [App\Http\Controllers\Admin\SettingController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('/settings/themes', [AdminThemeController::class, 'index'])->name('settings.themes');
    Route::match(['put', 'post'], '/settings/themes', [AdminThemeController::class, 'update'])->name('settings.themes.update');
    Route::get('/settings/logo', [App\Http\Controllers\Admin\SettingController::class, 'logo'])->name('settings.logo');
    Route::put('/settings/logo', [App\Http\Controllers\Admin\SettingController::class, 'updateLogo'])->name('settings.logo.update');
    Route::get('/settings/registration', [App\Http\Controllers\Admin\SettingController::class, 'registration'])->name('settings.registration');
    Route::put('/settings/registration', [App\Http\Controllers\Admin\SettingController::class, 'updateRegistration'])->name('settings.registration.update');
    Route::get('/settings/legal-imprint', [App\Http\Controllers\Admin\SettingController::class, 'legalImprint'])->name('settings.legal-imprint');
    Route::put('/settings/legal-imprint', [App\Http\Controllers\Admin\SettingController::class, 'updateLegalImprint'])->name('settings.legal-imprint.update');
    Route::get('/settings/languages', [App\Http\Controllers\Admin\SettingController::class, 'languages'])->name('settings.languages');
    Route::post('/settings/languages', [App\Http\Controllers\Admin\SettingController::class, 'storeLanguage'])->name('settings.languages.store');
    Route::put('/settings/languages', [App\Http\Controllers\Admin\SettingController::class, 'updateLanguage'])->name('settings.languages.update');
});
