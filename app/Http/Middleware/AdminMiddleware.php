<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * @var array<string, string>
     */
    protected array $permissionMap = [
        'admin.dashboard' => 'dashboard',
        'admin.search.*' => 'dashboard',
        'admin.notifications.*' => 'dashboard',
        'admin.news.*' => 'news',
        'admin.editorial-calendar.*' => 'news',
        'admin.comment-moderation.*' => 'news',
        'admin.ad-slots.*' => 'news',
        'admin.news-categories.*' => 'news',
        'admin.pages.*' => 'pages',
        'admin.forms.*' => 'forms',
        'admin.users.*' => 'users',
        'admin.team' => 'team',
        'admin.settings.*' => 'settings',
        'admin.plugins.*' => 'settings',
        'admin.operations.index' => 'operations',
        'admin.operations.schedule' => 'operations',
        'admin.operations.schedule.update' => 'operations',
        'admin.operations.schedule.run' => 'operations',
        'admin.operations.cli' => 'operations',
        'admin.operations.cli.execute' => 'operations',
        'admin.operations.backup' => 'operations',
        'admin.operations.migrate' => 'operations',
        'admin.operations.cache-clear' => 'operations',
        'admin.operations.maintenance.*' => 'operations',
        'admin.operations.dependencies.update' => 'operations',
        'admin.activity-log.*' => 'activity_log',
        'admin.login-history.*' => 'login_history',
        'admin.system-update.*' => 'system_update',
        'admin.operations.server-info' => 'server_info',
        'admin.operations.health-check' => 'health_check',
        'admin.operations.report.pdf' => 'operations',
        'admin.security.*' => 'security',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return redirect('/')->with('error', __('You do not have permission to access this page'));
        }

        $user = auth()->user();
        $routeName = (string) optional($request->route())->getName();
        $requiredPermission = $this->requiredPermissionForRoute($routeName);
        if ($requiredPermission !== null && ! $user->hasAdminPermission($requiredPermission)) {
            $fallbackRoute = $this->firstAllowedAdminRoute($user);
            if ($fallbackRoute !== null) {
                return redirect()->route($fallbackRoute)
                    ->with('error', __('You do not have permission to access this page'));
            }

            return redirect('/')
                ->with('error', __('You do not have permission to access this page'));
        }

        return $next($request);
    }

    protected function requiredPermissionForRoute(string $routeName): ?string
    {
        foreach ($this->permissionMap as $pattern => $permission) {
            if (Str::is($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }

    protected function firstAllowedAdminRoute($user): ?string
    {
        $preferredRoutes = [
            'dashboard' => 'admin.dashboard',
            'news' => 'admin.news.index',
            'pages' => 'admin.pages.index',
            'forms' => 'admin.forms.index',
            'users' => 'admin.users.index',
            'team' => 'admin.team',
            'settings' => 'admin.settings.general',
            'operations' => 'admin.operations.index',
            'activity_log' => 'admin.activity-log.index',
            'login_history' => 'admin.login-history.index',
            'system_update' => 'admin.system-update.index',
            'server_info' => 'admin.operations.server-info',
            'health_check' => 'admin.operations.health-check',
            'security' => 'admin.security.two-factor',
        ];

        foreach ($preferredRoutes as $permission => $routeName) {
            if ($user->hasAdminPermission($permission) && Route::has($routeName)) {
                return $routeName;
            }
        }

        return null;
    }
}
