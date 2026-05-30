<?php

namespace App\Http\Controllers;

use App\Support\LegalProfile;

class LegalController extends Controller
{
    public function imprint()
    {
        return view('legal.imprint', [
            'legal' => LegalProfile::resolved(),
        ]);
    }

    public function privacy()
    {
        return view('legal.privacy', [
            'legal' => LegalProfile::resolved(),
            'turnstileEnabled' => filled(config('services.cloudflare.turnstile.site_key')),
        ]);
    }

    public function terms()
    {
        return view('legal.terms');
    }
}
