<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class LoginHistoryController extends Controller
{
    private function applyFilters(Request $request, $query): void
    {
        if ($request->filled('success')) {
            $query->where('success', $request->boolean('success'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('identifier')) {
            $term = '%'.addcslashes($request->input('identifier'), '%_\\').'%';
            $query->where('identifier', 'like', $term);
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
        $query = LoginHistory::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);

        $histories = $query->paginate(50)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.login-history.index', compact('histories', 'users'));
    }

    public function exportPdf(Request $request)
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return back()->with('error', __('PDF export is currently unavailable. Please install dompdf via Composer.'));
        }

        $query = LoginHistory::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);
        $histories = $query->limit(2000)->get();
        $generatedAt = now();

        $html = view('admin.login-history.report-pdf', compact('histories', 'generatedAt'))->render();
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'login-history-'.$generatedAt->format('Ymd-His').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = LoginHistory::query()->with('user')->orderByDesc('created_at');
        $this->applyFilters($request, $query);
        $histories = $query->limit(20000)->get();
        $fileName = 'login-history-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($histories): void {
            $output = fopen('php://output', 'w');
            if (! $output) {
                return;
            }

            // UTF-8 BOM for proper Excel encoding detection.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Result', 'User', 'Identifier', 'Note', 'IP'], ';');

            foreach ($histories as $entry) {
                fputcsv($output, [
                    optional($entry->created_at)->format('Y-m-d H:i:s'),
                    $entry->success ? 'success' : 'failed',
                    $entry->user?->name ?? '',
                    $entry->identifier ?? '',
                    $entry->failure_reason ?? '',
                    $entry->ip_address ?? '',
                ], ';');
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
