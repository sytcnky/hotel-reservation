// resources/js/pages/hotel-details.js
import { initDatePicker } from '../ui/date-picker';

export function initHotelDetails() {
    const hasRoomCards = document.querySelectorAll('.room-card').length > 0;
    if (!hasRoomCards) return;

    initRoomToggles();
    initDateRangePicker();

    initBookingFormSync();
    initVideoModalReset();
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    initDatePicker({
        el: input,
        contract: 'hotel_details_range_ymd_alt',
        locale: document.documentElement.lang,
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

function initBookingFormSync() {
    const form = document.getElementById('booking-form');
    if (!form) return;

    const adultInput   = form.querySelector('input[data-type="adult"]');
    const childInput   = form.querySelector('input[data-type="child"]');
    const hiddenAdults = form.querySelector('#adultsInput');
    const hiddenChilds = form.querySelector('#childrenInput');
    const guestInput   = document.getElementById('guestInput');

    if (!adultInput || !childInput || !hiddenAdults || !hiddenChilds) {
        return;
    }

    const initialAdults   = parseInt(form.dataset.initialAdults || '0', 10);
    const initialChildren = parseInt(form.dataset.initialChildren || '0', 10);

    function updateGuestDisplay() {
        if (!guestInput) return;

        const a = parseInt(adultInput.value || '0', 10);
        const c = parseInt(childInput.value || '0', 10);

        const parts = [];
        if (a > 0) parts.push(a + ' Yetişkin');
        if (c > 0) parts.push(c + ' Çocuk');

        guestInput.value = parts.join(', ');
    }

    function syncHidden() {
        hiddenAdults.value = adultInput.value || '0';
        hiddenChilds.value = childInput.value || '0';
    }

    // İlk yükleme
    adultInput.value   = String(initialAdults);
    childInput.value   = String(initialChildren);
    hiddenAdults.value = String(initialAdults);
    hiddenChilds.value = String(initialChildren);
    updateGuestDisplay();

    // Submit öncesi hidden sync
    form.addEventListener('submit', function () {
        syncHidden();
    });
}

function initVideoModalReset() {
    const modal = document.getElementById('hotelVideoModal');
    const iframe = document.getElementById('hotelVideoFrame');

    if (!modal || !iframe) return;

    const originalSrc = iframe.getAttribute('src') || '';

    modal.addEventListener('hidden.bs.modal', function () {
        iframe.setAttribute('src', '');
        setTimeout(() => {
            iframe.setAttribute('src', originalSrc);
        }, 30);
    });
}
