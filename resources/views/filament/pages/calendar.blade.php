<x-filament-panels::page>
    @php
        $uiLocale = app()->getLocale();
    @endphp

    {{-- Filters (Filament schema) --}}
    <div class="mb-4">
        {{ $this->filtersSchema }}
    </div>

    <div class="fi-color fi-color-primary">
        <div
            wire:ignore
            id="calendar"
            data-locale="{{ $uiLocale }}"
            style="min-height:700px"
        ></div>
    </div>

    <script>
        (function () {
            let calendarInstance = null;

            function getFiltersFromLivewire(lw) {
                try {
                    if (lw && typeof lw.get === 'function') {
                        return lw.get('filters') || {};
                    }
                    if (lw && lw.$wire && typeof lw.$wire.get === 'function') {
                        return lw.$wire.get('filters') || {};
                    }
                } catch (e) {}
                return {};
            }

            function bootCalendar() {
                const calendarEl = document.getElementById('calendar');
                if (!calendarEl) return;

                if (!window.FullCalendar || typeof window.FullCalendar.Calendar !== 'function') {
                    setTimeout(bootCalendar, 50);
                    return;
                }

                if (!window.FullCalendar.globalLocales || window.FullCalendar.globalLocales.length === 0) {
                    setTimeout(bootCalendar, 50);
                    return;
                }

                if (calendarInstance) return;

                if (!window.Livewire || typeof window.Livewire.find !== 'function') {
                    setTimeout(bootCalendar, 50);
                    return;
                }

                const lwRoot = calendarEl.closest('[wire\\:id]');
                const wireId = lwRoot ? lwRoot.getAttribute('wire:id') : null;
                const lw = wireId ? window.Livewire.find(wireId) : null;

                if (!lw) {
                    setTimeout(bootCalendar, 50);
                    return;
                }

                const rawLocale = (calendarEl.dataset.locale || 'en').toLowerCase();
                const fcLocale = (rawLocale === 'tr') ? 'tr' : 'en';

                calendarInstance = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',

                    locales: window.FullCalendar.globalLocales,
                    locale: fcLocale,

                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },

                    events: async (info, successCallback, failureCallback) => {
                        try {
                            const start = (info.startStr || '').slice(0, 10);
                            const end   = (info.endStr   || '').slice(0, 10);

                            const filters = getFiltersFromLivewire(lw);

                            const events = await lw.call('getCalendarEvents', start, end, filters);
                            successCallback(Array.isArray(events) ? events : []);
                        } catch (e) {
                            console.error(e);
                            failureCallback(e);
                        }
                    },

                    eventClick: (info) => {
                        const url = info?.event?.extendedProps?.orderUrl;
                        if (url) window.location.href = url;
                    },
                });

                calendarInstance.render();

                // Livewire filter değişince: refetch
                window.addEventListener('calendar:refetch', function () {
                    try {
                        if (calendarInstance) {
                            calendarInstance.refetchEvents();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            }

            document.addEventListener('livewire:init', bootCalendar);

            document.addEventListener('livewire:navigated', function () {
                calendarInstance = null;
                bootCalendar();
            });

            setTimeout(bootCalendar, 0);
        })();
    </script>
</x-filament-panels::page>
