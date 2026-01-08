// resources/js/pages/hotel-details.js

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

export function initHotelDetails() {
    initRoomToggles();
    initDateRangePicker();
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    // Locale
    flatpickr.localize(Turkish);

    flatpickr(input, {
        mode: 'range',
        dateFormat: 'd.m.Y',
        minDate: 'today',
        // Otel controller'daki parseDateRange ile uyumlu:
        // "18.11.2025 - 22.11.2025"
        allowInput: true,
    });
}

function initRoomToggles() {
    const roomCards = document.querySelectorAll('.room-card');
    if (!roomCards.length) return;

    roomCards.forEach((card) => {
        const toggleBtn = card.querySelector('.room-toggle-details');
        const wrapper   = card.querySelector('.room-details-wrapper');
        const content   = card.querySelector('.room-details');
        const gallery   = card.querySelector('.gallery');

        if (!toggleBtn || !wrapper || !content) return;

        toggleBtn.addEventListener('click', () => {
            const isOpen = card.classList.toggle('expanded');
            toggleBtn.classList.toggle('bg-warning', isOpen);
            toggleBtn.classList.toggle('bg-white', !isOpen);

            if (isOpen) {
                if (gallery && !gallery.dataset.initialized) {
                    import('../ui/gallery').then(({ default: initGallery }) => {
                        initGallery(gallery);
                    });
                }

                wrapper.style.height = content.scrollHeight + 'px';

                wrapper.addEventListener('transitionend', () => {
                    if (card.classList.contains('expanded')) {
                        wrapper.style.height = 'auto';
                    }
                }, { once: true });

            } else {
                wrapper.style.height = content.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    wrapper.style.height = '0px';
                });
            }
        });
    });
}
