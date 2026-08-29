<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrlRedirect;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        $redirects = UrlRedirect::query()
            ->orderByDesc('updated_at')
            ->paginate(25);

        return view('admin.redirects.index', compact('redirects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['from_path'] = UrlRedirect::normalizeFromPath($data['from_path']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['hits'] = 0;

        UrlRedirect::query()->create($data);
        $this->forgetCache();
        ActivityLogger::log('redirects.created', __('Redirect created: :from', ['from' => $data['from_path']]));

        return back()->with('success', __('Redirect saved.'));
    }

    public function update(Request $request, UrlRedirect $redirect): RedirectResponse
    {
        $data = $this->validated($request, $redirect->id);
        $data['from_path'] = UrlRedirect::normalizeFromPath($data['from_path']);
        $data['is_active'] = $request->boolean('is_active');

        $redirect->update($data);
        $this->forgetCache();
        ActivityLogger::log('redirects.updated', __('Redirect updated: :from', ['from' => $data['from_path']]));

        return back()->with('success', __('Redirect updated.'));
    }

    public function destroy(UrlRedirect $redirect): RedirectResponse
    {
        $from = $redirect->from_path;
        $redirect->delete();
        $this->forgetCache();
        ActivityLogger::log('redirects.deleted', __('Redirect deleted: :from', ['from' => $from]));

        return back()->with('success', __('Redirect deleted.'));
    }

    /**
     * @return array{from_path:string,to_url:string,status_code:int,notes:?string}
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'from_path' => UrlRedirect::normalizeFromPath((string) $request->input('from_path', '')),
        ]);

        $data = $request->validate([
            'from_path' => [
                'required',
                'string',
                'max:500',
                Rule::unique('url_redirects', 'from_path')->ignore($ignoreId),
            ],
            'to_url' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $from = UrlRedirect::normalizeFromPath((string) $data['from_path']);
        $to = trim((string) $data['to_url']);

        if (! preg_match('#^https?://#i', $to)) {
            $to = UrlRedirect::normalizeFromPath($to);
        }

        if ($from === '/' || $from === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'from_path' => __('The source path cannot be the homepage root alone in a conflicting way; use a specific old path.'),
            ]);
        }

        return [
            'from_path' => $from,
            'to_url' => $to,
            'status_code' => (int) $data['status_code'],
            'notes' => isset($data['notes']) ? trim((string) $data['notes']) : null,
        ];
    }

    private function forgetCache(): void
    {
        Cache::forget('cms.url_redirects.map');
    }
}
