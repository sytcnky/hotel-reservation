// resources/js/pages/hotel-details.js
import { initDatePicker } from '../ui/date-picker';

export function initHotelDetails() {
    const hasRoomCards = document.querySelectorAll('.room-card').length > 0;
    if (!hasRoomCards) return;

    initRoomToggles();
    initDateRangePicker();
    initBookingFormSync();
    initBookingFormValidation();
    initVideoModalReset();
}

// -------------------------------------------------
// Date invalid helpers (flatpickr altInput aware)
// -------------------------------------------------
function getDateVisibleInput(input) {
    if (!input) return null;
    const fp = input._flatpickr;
    return fp && fp.altInput ? fp.altInput : input;
}

function addDateInvalid(input) {
    if (!input) return;
    input.classList.add('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.add('is-invalid');
    }
}

function clearDateInvalid(input) {
    if (!input) return;
    input.classList.remove('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.remove('is-invalid');
    }
}

function bindDateInvalidClear(input) {
    if (!input) return;

    const vis = getDateVisibleInput(input);
    if (!vis) return;

    const handler = () => {
        const v = (vis.value || '').trim();
        if (v) clearDateInvalid(input);
    };

    vis.addEventListener('input', handler);
    vis.addEventListener('change', handler);

    const fp = input._flatpickr;
    if (fp) {
        fp.config.onChange = (fp.config.onChange || []).concat(() => clearDateInvalid(input));
    }
}

function initDateRangePicker() {
    const input = document.getElementById('checkin');
    if (!input) return;

    initDatePicker({
        el: input,
        contract: 'hotel_details_range_ymd_alt',
        locale: document.documentElement.lang,
    });

    // tarih seçince kırmızı kalksın (altInput dahil)
    bindDateInvalidClear(input);
}

// -------------------------------------------------
// Booking form validation (GET form; bootstrap-like red border only)
// -------------------------------------------------
function initBookingFormValidation() {
    const form = document.getElementById('booking-form');
    if (!form) return;

    const checkin = document.getElementById('checkin');
    if (!checkin) return;

    // submit'te checkin zorunlu
    form.addEventListener('submit', (e) => {
        const vis = getDateVisibleInput(checkin);
        const val = (vis?.value || checkin.value || '').trim();

        if (!val) {
            e.preventDefault();
            e.stopPropagation();
            addDateInvalid(checkin);
        }
    });

    // kullanıcı sonradan seçerse temizle
    bindDateInvalidClear(checkin);
}

function initRoomToggles() {
    const roomCards = document.querySelectorAll('.room-card');
    if (!roomCards.length) return;

    roomCards.forEach((card) => {
        const toggleBtn = card.querySelector('.room-toggle-details');
        const wrapper = card.querySelector('.room-details-wrapper');
        const content = card.querySelector('.room-details');
        const gallery = card.querySelector('.gallery');

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

                wrapper.addEventListener(
                    'transitionend',
                    () => {
                        if (card.classList.contains('expanded')) {
                            wrapper.style.height = 'auto';
                        }
                    },
                    { once: true }
                );
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

    const adultInput = form.querySelector('input[data-type="adult"]');
    const childInput = form.querySelector('input[data-type="child"]');
    const hiddenAdults = form.querySelector('#adultsInput');
    const hiddenChilds = form.querySelector('#childrenInput');
    const guestInput = document.getElementById('guestInput');

    if (!adultInput || !childInput || !hiddenAdults || !hiddenChilds) {
        return;
    }

    const initialAdults = parseInt(form.dataset.initialAdults || '0', 10);
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

    adultInput.value = String(initialAdults);
    childInput.value = String(initialChildren);
    hiddenAdults.value = String(initialAdults);
    hiddenChilds.value = String(initialChildren);
    updateGuestDisplay();

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
