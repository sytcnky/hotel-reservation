import { initDatePicker } from '../ui/date-picker';

export function initVillaDetails() {
    const box = document.getElementById('villa-price-box');
    const dateInput = document.getElementById('checkin');

    // Villa sayfası değilse çık
    if (!box || !dateInput) {
        return;
    }

    initGuestSync();
    initPricingPreview(box, dateInput);
}

/**
 * Guests UI -> hidden sync (snapshot: adults/children)
 */
function initGuestSync() {
    const form = document.getElementById('villa-booking-form');
    if (!form) return;

    const adultInput = form.querySelector('input[data-type="adult"]');
    const childInput = form.querySelector('input[data-type="child"]');
    const hiddenAdults = form.querySelector('#adultsInput');
    const hiddenChilds = form.querySelector('#childrenInput');
    const guestInput = document.getElementById('guestInput');

    function updateGuestDisplay() {
        if (!guestInput) return;

        const a = parseInt(adultInput?.value || '0', 10);
        const c = parseInt(childInput?.value || '0', 10);

        const labelAdult = guestInput.dataset.labelAdult || '';
        const labelChild = guestInput.dataset.labelChild || '';
        const placeholder = guestInput.dataset.placeholder || guestInput.getAttribute('placeholder') || '';

        const parts = [];
        if (a > 0 && labelAdult) parts.push(`${a} ${labelAdult}`);
        if (c > 0 && labelChild) parts.push(`${c} ${labelChild}`);

        guestInput.value = parts.length ? parts.join(', ') : placeholder;
    }

    function syncHidden() {
        if (hiddenAdults && adultInput) hiddenAdults.value = adultInput.value || '0';
        if (hiddenChilds && childInput) hiddenChilds.value = childInput.value || '0';
    }

    syncHidden();
    updateGuestDisplay();

    form.addEventListener('click', (e) => {
        const btn = e.target?.closest?.('button.plus, button.minus');
        if (!btn) return;

        setTimeout(() => {
            syncHidden();
            updateGuestDisplay();
        }, 0);
    });

    form.addEventListener('submit', () => {
        syncHidden();
    });
}

/**
 * Pricing PREVIEW only (no snapshot, no authority)
 * + min/max gecede buton disable/enable
 */
function initPricingPreview(box, dateInput) {
    const form = document.getElementById('villa-booking-form');
    const btnSubmit = document.getElementById('villaAddToCartBtn');

    // UI locale (fallback YOK)
    const uiLocale = (document.documentElement.lang || '').trim();
    if (!uiLocale) {
        return;
    }

    // Server’dan gelen preview verileri (otorite DEĞİL)
    const basePrice = parseFloat(box.dataset.price || '');
    const prepayRate = parseFloat(box.dataset.prepayment || '0');
    const minNights = box.dataset.minNights ? parseInt(box.dataset.minNights, 10) : 0;
    const maxNights = box.dataset.maxNights ? parseInt(box.dataset.maxNights, 10) : 0;

    // UI elemanları
    const elBefore = document.getElementById('price-before-selection');
    const elAfter = document.getElementById('price-after-selection');
    const elNightly = document.getElementById('price-nightly');
    const elNights = document.getElementById('price-nights');
    const elPrepayment = document.getElementById('price-prepayment');
    const elTotal = document.getElementById('price-total');
    const elMinNights = document.getElementById('min-nights-feedback');
    const elMaxNights = document.getElementById('max-nights-feedback');

    // Hidden (yalnızca tarih)
    const hiddenCheckin = document.getElementById('hidden-checkin');
    const hiddenCheckout = document.getElementById('hidden-checkout');

    // Bu sayfada price yoksa zaten disabled geliyor; dokunmayalım
    const hasPrice = Number.isFinite(basePrice) && basePrice > 0;

    function setButtonEnabled(enabled) {
        if (!btnSubmit) return;
        if (!hasPrice) return; // fiyat yoksa hep disabled
        btnSubmit.disabled = !enabled;
    }

    function formatNumber(value) {
        const n = Number(value);
        if (!Number.isFinite(n)) return '';
        return new Intl.NumberFormat(uiLocale, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(n);
    }

    function resetState() {
        if (elBefore) elBefore.classList.remove('d-none');
        if (elAfter) elAfter.classList.add('d-none');
        if (elMinNights) elMinNights.classList.add('d-none');
        if (elMaxNights) elMaxNights.classList.add('d-none');

        if (hiddenCheckin) hiddenCheckin.value = '';
        if (hiddenCheckout) hiddenCheckout.value = '';

        setInvalid(false);
        setButtonEnabled(false);
    }

    function setInvalid(on) {
        if (!dateInput) return;
        dateInput.classList.toggle('is-invalid', !!on);
    }

    function hasSelection() {
        return !!(hiddenCheckin && hiddenCheckin.value && hiddenCheckin.value.trim());
    }

    initDatePicker({
        el: dateInput,
        contract: 'villa_range_ui_dmy_alt',
        locale: uiLocale,
        hooks: {
            onClear: () => {
                resetState();
            },
            onValidChange: (p) => {
                const nights = Number(p?.nights || 0);

                // mesajları önce kapat
                if (elMinNights) elMinNights.classList.add('d-none');
                if (elMaxNights) elMaxNights.classList.add('d-none');

                let invalid = false;

                if (minNights && nights < minNights) {
                    invalid = true;
                    if (elMinNights) elMinNights.classList.remove('d-none');
                }

                if (maxNights && nights > maxNights) {
                    invalid = true;
                    if (elMaxNights) elMaxNights.classList.remove('d-none');
                }

                if (invalid) {
                    if (elBefore) elBefore.classList.remove('d-none');
                    if (elAfter) elAfter.classList.add('d-none');

                    if (hiddenCheckin) hiddenCheckin.value = '';
                    if (hiddenCheckout) hiddenCheckout.value = '';

                    setInvalid(true);
                    setButtonEnabled(false);
                    return;
                }

                // PREVIEW hesaplama
                const total = Number.isFinite(basePrice) ? basePrice * nights : 0;
                const prepay = Math.round(total * (prepayRate / 100));

                if (elNightly) elNightly.textContent = formatNumber(basePrice);
                if (elNights) elNights.textContent = String(nights);
                if (elPrepayment) elPrepayment.textContent = formatNumber(prepay);
                if (elTotal) elTotal.textContent = formatNumber(total);

                if (elBefore) elBefore.classList.add('d-none');
                if (elAfter) elAfter.classList.remove('d-none');

                const raw = (dateInput.value || '').trim(); // "YYYY-mm-dd - YYYY-mm-dd"
                const parts = raw.split(' - ').map((s) => s.trim());
                if (hiddenCheckin) hiddenCheckin.value = parts[0] || '';
                if (hiddenCheckout) hiddenCheckout.value = parts[1] || '';

                setInvalid(false);
                setButtonEnabled(true);
            },
        },
    });

    // ilk yükleme: seçim yok → disabled
    resetState();

    // submit guard: seçim yoksa engelle + disabled kalsın
    if (form) {
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', (e) => {
            if (!hasSelection()) {
                setInvalid(true);
                setButtonEnabled(false);
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
}
