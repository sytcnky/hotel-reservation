// resources/js/pages/hotel-listing.js
import { initDatePicker } from '../ui/date-picker';

export function initHotelListing() {
    initDateRangePicker();
    initSortSync();
    initFilterToggle();
}

/* =====================================================
   Helpers (Flatpickr + is-invalid)
   - Flatpickr altInput kullanıyorsa, is-invalid hem input'tan
     hem altInput'tan temizlenir.
===================================================== */
function clearDateInvalid(input) {
    if (!input) return;

    input.classList.remove('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.remove('is-invalid');
    }
}

function markDateInvalid(input) {
    if (!input) return;

    input.classList.add('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.add('is-invalid');
    }
}

function bindDateInvalidClear(input) {
    if (!input) return;

    const fp = input._flatpickr;
    const vis = fp && fp.altInput ? fp.altInput : input;

    const handler = () => {
        const val = (vis.value || '').trim();
        if (val) clearDateInvalid(input);
    };

    vis.addEventListener('input', handler);
    vis.addEventListener('change', handler);

    if (fp) {
        fp.config.onChange = (fp.config.onChange || []).concat(() => clearDateInvalid(input));
    }
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    const params = new URLSearchParams(window.location.search);
    const raw = (params.get('checkin') || '').trim();

    let initialValue = null;
    if (raw.includes(' - ')) {
        const [start, end] = raw.split(' - ').map((s) => s.trim());
        if (start && end) {
            initialValue = `${start} - ${end}`;
        }
    }

    initDatePicker({
        el: input,
        contract: 'hotel_listing_range',
        locale: document.documentElement.lang,
        initialValue,
    });

    // altInput dahil invalid temizliği
    bindDateInvalidClear(input);

    // listing filter form submit'inde boşsa kırmızı
    const form = document.getElementById('hotelFilterForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            const visible = input?._flatpickr?.altInput || input;
            const val = (visible?.value || '').trim();

            if (!val) {
                e.preventDefault();
                e.stopPropagation();
                markDateInvalid(input);
            } else {
                clearDateInvalid(input);
            }
        });
    }
}

function initSortSync() {
    const select = document.getElementById('sortBySelect');
    const form = document.getElementById('hotelFilterForm');
    if (!select || !form) return;

    // Page load: URL -> select
    const params = new URLSearchParams(window.location.search);
    const current = params.get('sort_by') || '';
    select.value = current;

    // Change: select -> form hidden -> submit
    select.addEventListener('change', () => {
        let input = form.querySelector('input[name="sort_by"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sort_by';
            form.appendChild(input);
        }

        input.value = select.value || '';
        form.submit();
    });
}

function initFilterToggle() {
    const btn = document.getElementById('toggleFilterBtn');
    const filterCol = document.getElementById('filterCol');
    const listingCol = document.getElementById('listingCol');
    if (!btn || !filterCol || !listingCol) return;

    let open = true;

    btn.addEventListener('click', () => {
        open = !open;

        filterCol.classList.toggle('d-none', !open);

        if (open) {
            listingCol.classList.remove('col-xl-12');
            listingCol.classList.add('col-xl-9');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            listingCol.classList.remove('col-xl-9');
            listingCol.classList.add('col-xl-12');
            btn.setAttribute('aria-expanded', 'false');
        }
    });
}
