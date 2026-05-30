<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    private function applyFilters(Request $request, $query): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $term = '%'.addcslashes($request->input('action'), '%_\\').'%';
            $query->where('action', 'like', $term);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from')->format('Y-m-d'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to')->format('Y-m-d'));
        }
    }

    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);

        $logs = $query->paginate(40)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.activity-log.index', compact('logs', 'users'));
    }

    public function exportPdf(Request $request)
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return back()->with('error', __('PDF export is currently unavailable. Please install dompdf via Composer.'));
        }

        $query = ActivityLog::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);
        $logs = $query->limit(2000)->get();
        $generatedAt = now();

        $html = view('admin.activity-log.report-pdf', compact('logs', 'generatedAt'))->render();
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'activity-log-'.$generatedAt->format('Ymd-His').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = ActivityLog::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);
        $logs = $query->limit(20000)->get();
        $fileName = 'activity-log-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($logs): void {
            $output = fopen('php://output', 'w');
            if (! $output) {
                return;
            }

            // UTF-8 BOM for proper Excel encoding detection.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'User', 'Action', 'Description', 'JSON', 'IP'], ';');

            foreach ($logs as $log) {
                fputcsv($output, [
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user?->name ?? '',
                    $log->action ?? '',
                    $log->description ?? '',
                    $log->properties ? json_encode($log->properties, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
                    $log->ip_address ?? '',
                ], ';');
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
