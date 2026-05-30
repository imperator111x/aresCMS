<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('System report') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        .muted { color: #6b7280; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .badge-ok { color: #065f46; font-weight: bold; }
        .badge-warn { color: #92400e; font-weight: bold; }
        .badge-fail { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ __('System report') }}</h1>
    <p class="muted">{{ __('Generated at: :time', ['time' => $generatedAt->format('d.m.Y H:i:s')]) }}</p>

    <h2>{{ __('Health summary') }}</h2>
    <table>
        <tr>
            <th>{{ __('OK') }}</th>
            <th>{{ __('Warnings') }}</th>
            <th>{{ __('Failures') }}</th>
        </tr>
        <tr>
            <td>{{ $summary['ok'] }}</td>
            <td>{{ $summary['warn'] }}</td>
            <td>{{ $summary['fail'] }}</td>
        </tr>
    </table>

    <h2>{{ __('Health checks') }}</h2>
    <table>
        <tr>
            <th>{{ __('Check') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Message') }}</th>
        </tr>
        @foreach($checks as $check)
            @php
                $class = $check['status'] === 'ok' ? 'badge-ok' : ($check['status'] === 'warn' ? 'badge-warn' : 'badge-fail');
            @endphp
            <tr>
                <td>{{ $check['name'] }}</td>
                <td class="{{ $class }}">{{ strtoupper((string) $check['status']) }}</td>
                <td>{{ $check['message'] }}</td>
            </tr>
        @endforeach
    </table>

    <h2>{{ __('System values') }}</h2>
    <table>
        <tr>
            <th>{{ __('Key') }}</th>
            <th>{{ __('Value') }}</th>
        </tr>
        @foreach($serverInfo as $key => $value)
            <tr>
                <td>{{ __(str_replace('_', ' ', ucfirst($key))) }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <h2>{{ __('Scheduled jobs') }}</h2>
    <table>
        <tr>
            <th>{{ __('Job') }}</th>
            <th>{{ __('Enabled') }}</th>
            <th>{{ __('Frequency') }}</th>
            <th>{{ __('Time (HH:MM)') }}</th>
            <th>{{ __('Last run') }}</th>
            <th>{{ __('Last exit code') }}</th>
            <th>{{ __('Last duration') }}</th>
        </tr>
        @foreach($jobs as $jobKey => $job)
            @php
                $cfg = $configs[$jobKey] ?? [];
                $st = $statuses[$jobKey] ?? [];
            @endphp
            <tr>
                <td>{{ $job['label'] }}</td>
                <td>{{ !empty($cfg['enabled']) ? __('Yes') : __('No') }}</td>
                <td>{{ $cfg['frequency'] ?? '-' }}</td>
                <td>{{ $cfg['time'] ?? '-' }}</td>
                <td>{{ $st['last_run_at'] ?? '-' }}</td>
                <td>{{ $st['last_exit_code'] ?? '-' }}</td>
                <td>{{ isset($st['last_duration_ms']) ? $st['last_duration_ms'].' ms' : '-' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
