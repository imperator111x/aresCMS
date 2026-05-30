<?php

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Plugins\SponsorAdSlots\Models\AdSlot;
use Plugins\SponsorAdSlots\Services\AdSlotService;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/ad-slots')
    ->name('admin.ad-slots.')
    ->group(function (): void {
        Route::get('/', function () {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }
            $availableSlots = app(AdSlotService::class)->availableSlots();

            if (! Schema::hasTable('sponsor_ad_slots')) {
                $empty = new LengthAwarePaginator([], 0, 20);
                $empty->setPath(request()->url());

                return view()->file(__DIR__.'/../resources/views/index.blade.php', [
                    'adSlots' => $empty,
                    'availableSlots' => $availableSlots,
                ])->with('info', __('Run migrations to enable ad slots table.'));
            }

            $adSlots = AdSlot::query()->orderBy('slot_key')->orderBy('priority')->paginate(20);

            return view()->file(__DIR__.'/../resources/views/index.blade.php', compact('adSlots', 'availableSlots'));
        })->name('index');

        Route::post('/', function (Request $request) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            $data = $request->validate([
                'name' => ['required', 'string', 'max:190'],
                'slot_key' => ['required', 'string', 'max:100'],
                'target_url' => ['nullable', 'url', 'max:2048'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image_file' => ['nullable', 'image', 'max:5120'],
                'html_code' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date'],
                'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            ]);

            $data['is_active'] = $request->has('is_active');
            $data['slot_key'] = trim((string) $data['slot_key']);
            $data['priority'] = (int) ($data['priority'] ?? 100);
            if ($request->hasFile('image_file')) {
                $data['image_path'] = (string) $request->file('image_file')->store('ad-slots', 'public');
            }
            unset($data['image_file']);

            AdSlot::query()->create($data);

            return back()->with('success', __('Ad slot created.'));
        })->name('store');

        Route::put('/{adSlot}', function (Request $request, AdSlot $adSlot) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            $data = $request->validate([
                'name' => ['required', 'string', 'max:190'],
                'slot_key' => ['required', 'string', 'max:100'],
                'target_url' => ['nullable', 'url', 'max:2048'],
                'image_url' => ['nullable', 'url', 'max:2048'],
                'image_file' => ['nullable', 'image', 'max:5120'],
                'html_code' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date'],
                'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            ]);

            $data['is_active'] = $request->has('is_active');
            $data['slot_key'] = trim((string) $data['slot_key']);
            $data['priority'] = (int) ($data['priority'] ?? 100);
            if ($request->hasFile('image_file')) {
                if (is_string($adSlot->image_path) && $adSlot->image_path !== '') {
                    Storage::disk('public')->delete($adSlot->image_path);
                }
                $data['image_path'] = (string) $request->file('image_file')->store('ad-slots', 'public');
            }
            unset($data['image_file']);
            $adSlot->update($data);

            return back()->with('success', __('Ad slot updated.'));
        })->name('update');

        Route::delete('/{adSlot}', function (AdSlot $adSlot) {
            if (! auth()->user()->hasAdminPermission('news')) {
                abort(403);
            }

            if (is_string($adSlot->image_path) && $adSlot->image_path !== '') {
                Storage::disk('public')->delete($adSlot->image_path);
            }
            $adSlot->delete();

            return back()->with('success', __('Ad slot deleted.'));
        })->name('destroy');
    });

Route::middleware('web')->get('/ads/click/{adSlot}', function (AdSlot $adSlot) {
    if (Schema::hasTable('sponsor_ad_slots')) {
        $adSlot->increment('clicks');
    }

    $url = is_string($adSlot->target_url) && trim($adSlot->target_url) !== '' ? $adSlot->target_url : url('/');

    return redirect()->away($url);
})->name('ads.click');
