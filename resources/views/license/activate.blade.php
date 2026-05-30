<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('License required') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: #0f172a;
            color: #e2e8f0;
        }
        .card {
            max-width: 28rem;
            width: 100%;
            padding: 2rem;
            border-radius: 1rem;
            background: #1e293b;
            border: 1px solid #334155;
        }
        h1 { font-size: 1.25rem; margin: 0 0 1rem; color: #f8fafc; }
        p { margin: 0; line-height: 1.6; color: #94a3b8; font-size: 0.9375rem; }
        .alert {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: #7f1d1d33;
            border: 1px solid #991b1b66;
            color: #fecaca;
            font-size: 0.875rem;
        }
        label { display: block; margin-top: 1.25rem; margin-bottom: 0.35rem; font-size: 0.8125rem; font-weight: 600; color: #cbd5e1; }
        input[type="text"] {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border-radius: 0.5rem;
            border: 1px solid #475569;
            background: #0f172a;
            color: #f8fafc;
            font-size: 0.9375rem;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }
        button {
            margin-top: 1.25rem;
            width: 100%;
            padding: 0.65rem 1rem;
            border: none;
            border-radius: 0.5rem;
            background: #6366f1;
            color: #fff;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #4f46e5; }
        .hint { margin-top: 1rem; font-size: 0.8125rem; color: #64748b; }
        code { font-size: 0.8125rem; background: #0f172a; padding: 0.15rem 0.4rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('License required') }}</h1>

        @if($envKeySet)
            <p>{{ __('License key is set in the environment file and cannot be changed here.') }}</p>
            <p class="hint">{{ __('If the site still shows this page, check CMS_LICENSE_KEY in :env and your domain at :host.', ['env' => '.env', 'host' => 'key.luetcke.eu']) }}</p>
        @else
            <p>{{ __('Enter the license key for this domain. The key must be registered for your hostname at :host (including :local).', ['host' => 'key.luetcke.eu', 'local' => 'localhost']) }}</p>

            @if(!empty($alert))
                <div class="alert" role="alert">{{ $alert }}</div>
            @endif

            @error('license_key')
                <div class="alert" role="alert">{{ $message }}</div>
            @enderror

            <form method="post" action="{{ route('license.store') }}" autocomplete="off">
                @csrf
                <label for="license_key">{{ __('License key') }}</label>
                <input
                    type="text"
                    id="license_key"
                    name="license_key"
                    value="{{ old('license_key') }}"
                    required
                    maxlength="512"
                    placeholder="{{ __('Paste your key here') }}"
                    spellcheck="false"
                >
                <button type="submit">{{ __('Activate license') }}</button>
            </form>

            <p class="hint">{{ __('Alternatively you can set :key in :env.', ['key' => 'CMS_LICENSE_KEY', 'env' => '.env']) }}</p>
        @endif
    </div>
</body>
</html>
