@extends('layouts.admin')

@section('title', 'Editorial Calendar')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editorial Calendar</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Plane und verschiebe News per Drag & Drop.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-end gap-3 mb-4">
            <div>
                <label for="calendar-status-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select id="calendar-status-filter" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm">
                    <option value="">Alle</option>
                    <option value="draft">Entwurf</option>
                    <option value="scheduled">Geplant</option>
                    <option value="published">Veröffentlicht</option>
                </select>
            </div>
            <div>
                <label for="calendar-category-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kategorie</label>
                <select id="calendar-category-filter" class="px-3 py-2 rounded-lg border border-gray-300 dark:border-dark-600 bg-white dark:bg-dark-700 text-sm">
                    <option value="">Alle</option>
                </select>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Farben: <span class="text-slate-400">Entwurf</span> / <span class="text-amber-500">Geplant</span> / <span class="text-emerald-500">Veröffentlicht</span>
            </div>
        </div>

        <div id="editorial-calendar"></div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <style>
        #editorial-calendar .fc {
            color: inherit;
        }
        #editorial-calendar .fc-toolbar-title {
            font-size: 1.1rem;
            font-weight: 700;
        }
        #editorial-calendar .fc-event {
            cursor: pointer;
            border-width: 0;
            border-radius: 8px;
            padding: 2px 4px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('editorial-calendar');
            const statusFilter = document.getElementById('calendar-status-filter');
            const categoryFilter = document.getElementById('calendar-category-filter');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const loadEvents = async function (fetchInfo, successCallback, failureCallback) {
                try {
                    const query = new URLSearchParams({
                        status: statusFilter.value || '',
                        category: categoryFilter.value || '',
                    });
                    const res = await fetch(`{{ route('admin.editorial-calendar.events') }}?${query.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('events request failed');
                    const payload = await res.json();
                    if (Array.isArray(payload.categories)) {
                        const prev = categoryFilter.value;
                        categoryFilter.innerHTML = '<option value="">Alle</option>';
                        payload.categories.forEach(function (category) {
                            const opt = document.createElement('option');
                            opt.value = category;
                            opt.textContent = category;
                            categoryFilter.appendChild(opt);
                        });
                        categoryFilter.value = prev;
                    }
                    successCallback(payload.events || []);
                } catch (_e) {
                    failureCallback(_e);
                }
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'de',
                initialView: 'dayGridMonth',
                height: 'auto',
                firstDay: 1,
                editable: true,
                eventStartEditable: true,
                eventDurationEditable: false,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: loadEvents,
                eventDrop: async function (info) {
                    try {
                        const res = await fetch(`{{ url('/admin/editorial-calendar/news') }}/${info.event.id}/schedule`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                published_at: info.event.start?.toISOString()
                            })
                        });
                        if (!res.ok) throw new Error('save failed');
                    } catch (_e) {
                        info.revert();
                        alert('Termin konnte nicht gespeichert werden.');
                    }
                },
                eventClick: function (info) {
                    const editUrl = info.event.extendedProps?.editUrl;
                    if (editUrl) {
                        window.location.href = editUrl;
                    }
                }
            });

            statusFilter.addEventListener('change', function () {
                calendar.refetchEvents();
            });
            categoryFilter.addEventListener('change', function () {
                calendar.refetchEvents();
            });

            calendar.render();
        });
    </script>
@endpush

