<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Activity log') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .muted { color: #6b7280; font-size: 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ __('Activity log') }}</h1>
    <p class="muted">{{ __('Generated at: :time', ['time' => $generatedAt->format('d.m.Y H:i:s')]) }}</p>
    <table>
        <tr>
            <th>{{ __('When') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Action') }}</th>
            <th>{{ __('Details') }}</th>
            <th>{{ __('IP') }}</th>
        </tr>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                <td>{{ $log->user?->name ?? '—' }}</td>
                <td class="mono">{{ $log->action }}</td>
                <td>
                    {{ $log->description ?? '—' }}
                    @if($log->properties && count($log->properties))
                        <div class="mono">{{ json_encode($log->properties, JSON_UNESCAPED_UNICODE) }}</div>
                    @endif
                </td>
                <td>{{ $log->ip_address ?? '—' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
