<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Standard `*`: direkter Client (z. B. Cloudflare-Edge) darf X-Forwarded-* setzen — wichtig für HTTPS
     * und OAuth hinter CDN. Lokal abschalten: in `.env` z. B. `TRUSTED_PROXIES=false`.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $p = env('TRUSTED_PROXIES', '*');
        if ($p === false || $p === 'false' || $p === '0' || $p === '') {
            $this->proxies = null;
        } else {
            $this->proxies = $p;
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
