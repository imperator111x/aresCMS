<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Login history') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .muted { color: #6b7280; font-size: 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ __('Login history') }}</h1>
    <p class="muted">{{ __('Generated at: :time', ['time' => $generatedAt->format('d.m.Y H:i:s')]) }}</p>
    <table>
        <tr>
            <th>{{ __('When') }}</th>
            <th>{{ __('Result') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Identifier') }}</th>
            <th>{{ __('Note') }}</th>
            <th>{{ __('IP') }}</th>
        </tr>
        @foreach($histories as $h)
            <tr>
                <td>{{ $h->created_at?->format('d.m.Y H:i:s') }}</td>
                <td>{{ $h->success ? __('Success') : __('Failed') }}</td>
                <td>{{ $h->user?->name ?? '—' }}</td>
                <td>{{ $h->identifier }}</td>
                <td>{{ $h->failure_reason ?? '—' }}</td>
                <td>{{ $h->ip_address ?? '—' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
