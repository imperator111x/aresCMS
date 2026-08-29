<?php

namespace App\Http\Middleware;

use App\Services\CmsUpdateManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-Powered-By', 'aresCMS');
        $response->headers->set('X-CMS-Version', $this->cmsVersion());

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function cmsVersion(): string
    {
        try {
            return app(CmsUpdateManager::class)->getInstalledVersion();
        } catch (\Throwable) {
            return (string) config('cms.bundle_version', '0');
        }
    }
}
