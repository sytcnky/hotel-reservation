// resources/js/pages/hotel-listing.js
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

export function initHotelListing() {
    initDateRangePicker();
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    flatpickr.localize(Turkish);

    flatpickr(input, {
        mode: 'range',
        dateFormat: 'd.m.Y',
        minDate: 'today',
        allowInput: true,
    });
}
