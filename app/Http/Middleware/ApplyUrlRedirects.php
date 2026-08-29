<?php

namespace App\Http\Middleware;

use App\Models\UrlRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApplyUrlRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = $request->getPathInfo() ?: '/';
        if ($path !== '/' && str_starts_with($path, '/admin')) {
            return $next($request);
        }

        try {
            if (! Schema::hasTable('url_redirects')) {
                return $next($request);
            }
        } catch (\Throwable) {
            return $next($request);
        }

        $normalized = UrlRedirect::normalizeFromPath($path);

        /** @var array<string, array{to:string,code:int,id:int}> $map */
        $map = Cache::remember('cms.url_redirects.map', 60, static function (): array {
            $items = [];
            foreach (UrlRedirect::query()->where('is_active', true)->get(['id', 'from_path', 'to_url', 'status_code']) as $row) {
                $key = UrlRedirect::normalizeFromPath((string) $row->from_path);
                $items[$key] = [
                    'id' => (int) $row->id,
                    'to' => (string) $row->to_url,
                    'code' => in_array((int) $row->status_code, [301, 302, 307, 308], true)
                        ? (int) $row->status_code
                        : 301,
                ];
            }

            return $items;
        });

        if (! isset($map[$normalized])) {
            return $next($request);
        }

        $target = $map[$normalized];
        try {
            UrlRedirect::query()->whereKey($target['id'])->increment('hits');
        } catch (\Throwable) {
            // ignore hit counter failures
        }

        $to = $target['to'];
        if (! preg_match('#^https?://#i', $to)) {
            $to = url(UrlRedirect::normalizeFromPath($to));
        }

        return redirect()->to($to, $target['code']);
    }
}
