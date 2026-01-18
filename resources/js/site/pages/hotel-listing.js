// resources/js/pages/hotel-listing.js
import { initDatePicker } from '../ui/date-picker';

export function initHotelListing() {
    initDateRangePicker();
    initSortSync();
    initFilterToggle();
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    const params = new URLSearchParams(window.location.search);
    const raw = (params.get('checkin') || '').trim();

    let initialValue = null;
    if (raw.includes(' - ')) {
        const [start, end] = raw.split(' - ').map(s => s.trim());
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
