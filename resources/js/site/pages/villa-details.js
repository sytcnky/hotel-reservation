// resources/js/pages/villa-details.js
import { initDatePicker } from '../ui/date-picker';

export function initVillaDetails() {
    const box       = document.getElementById('villa-price-box');
    const dateInput = document.getElementById('checkin');

    // Villa sayfası değilse çık
    if (!box || !dateInput) {
        return;
    }

    initGuestSync();
    initPricing(box, dateInput);
}

function initGuestSync() {
    const form = document.getElementById('villa-booking-form');
    if (!form) return;

    const adultInput   = form.querySelector('input[data-type="adult"]');
    const childInput   = form.querySelector('input[data-type="child"]');
    const hiddenAdults = form.querySelector('#adultsInput');
    const hiddenChilds = form.querySelector('#childrenInput');
    const guestInput   = document.getElementById('guestInput');

    function updateGuestDisplay() {
        if (!guestInput) return;

        const a = parseInt(adultInput?.value || '0', 10);
        const c = parseInt(childInput?.value || '0', 10);

        const parts = [];
        if (a > 0) parts.push(a + ' Yetişkin');
        if (c > 0) parts.push(c + ' Çocuk');

        guestInput.value = parts.join(', ');
    }

    function syncHidden() {
        if (hiddenAdults && adultInput) {
            hiddenAdults.value = adultInput.value || 0;
        }
        if (hiddenChilds && childInput) {
            hiddenChilds.value = childInput.value || 0;
        }
    }

    // İlk state (blade zaten value basıyor; yine de garanti altına al)
    syncHidden();
    updateGuestDisplay();

    // +/- tıklanınca guestpicker input değerlerini değiştiriyor → display + hidden sync
    form.addEventListener('click', (e) => {
        const btn = e.target?.closest?.('button.plus, button.minus');
        if (!btn) return;

        // guestpicker click sonrası input değerleri değişmiş olacağından bir tick sonra oku
        setTimeout(() => {
            syncHidden();
            updateGuestDisplay();
        }, 0);
    });

    // Submit öncesi kesin senkron
    form.addEventListener('submit', () => {
        syncHidden();
    });
}

function initPricing(box, dateInput) {
    const basePrice  = parseFloat(box.dataset.price || '0');      // gecelik
    const currency   = box.dataset.currency || 'TRY';
    const prepayRate = parseFloat(box.dataset.prepayment || '0'); // yüzde

    const minNightsAttr = box.dataset.minNights;
    const maxNightsAttr = box.dataset.maxNights;

    const MIN_NIGHTS = minNightsAttr ? parseInt(minNightsAttr, 10) : 0;
    const MAX_NIGHTS = maxNightsAttr ? parseInt(maxNightsAttr, 10) : 0;

    const elBefore     = document.getElementById('price-before-selection');
    const elAfter      = document.getElementById('price-after-selection');
    const elNightly    = document.getElementById('price-nightly');
    const elNights     = document.getElementById('price-nights');
    const elPrepayment = document.getElementById('price-prepayment');
    const elTotal      = document.getElementById('price-total');
    const elMinNights  = document.getElementById('min-nights-feedback');
    const elMaxNights  = document.getElementById('max-nights-feedback');

    const hiddenCheckin      = document.getElementById('hidden-checkin');
    const hiddenCheckout     = document.getElementById('hidden-checkout');
    const hiddenNights       = document.getElementById('villa-nights');
    const hiddenPriceNightly = document.getElementById('villa-price-nightly');
    const hiddenPricePrepay  = document.getElementById('villa-price-prepayment');
    const hiddenPriceTotal   = document.getElementById('villa-price-total');

    function formatMoney(value) {
        return (
            new Intl.NumberFormat('tr-TR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value) +
            ' ' +
            currency
        );
    }

    function clearHidden() {
        if (hiddenCheckin) hiddenCheckin.value = '';
        if (hiddenCheckout) hiddenCheckout.value = '';
        if (hiddenNights) hiddenNights.value = '';
        if (hiddenPriceNightly) hiddenPriceNightly.value = '';
        if (hiddenPricePrepay) hiddenPricePrepay.value = '';
        if (hiddenPriceTotal) hiddenPriceTotal.value = '';
    }

    function resetState() {
        if (elBefore) elBefore.classList.remove('d-none');
        if (elAfter) elAfter.classList.add('d-none');

        if (elMinNights) elMinNights.classList.add('d-none');
        if (elMaxNights) elMaxNights.classList.add('d-none');

        clearHidden();
    }

    initDatePicker({
        el: dateInput,
        contract: 'excursion_single_ymd_alt',
        locale: document.documentElement.lang,
        hooks: {
            onClear: () => resetState(),
            onValidChange: (p) => {
                const nights = Number(p?.nights || 0);

                let invalid = false;

                if (MIN_NIGHTS && nights < MIN_NIGHTS) {
                    invalid = true;
                    if (elMinNights) elMinNights.classList.remove('d-none');
                } else if (elMinNights) {
                    elMinNights.classList.add('d-none');
                }

                if (MAX_NIGHTS && nights > MAX_NIGHTS) {
                    invalid = true;
                    if (elMaxNights) elMaxNights.classList.remove('d-none');
                } else if (elMaxNights) {
                    elMaxNights.classList.add('d-none');
                }

                if (invalid) {
                    if (elBefore) elBefore.classList.remove('d-none');
                    if (elAfter) elAfter.classList.add('d-none');
                    clearHidden();
                    return;
                }

                const total      = basePrice * nights;
                const prepayment = Math.round(total * (prepayRate / 100));

                if (elNightly) elNightly.textContent = formatMoney(basePrice);
                if (elNights) elNights.textContent = String(nights);
                if (elPrepayment) elPrepayment.textContent = formatMoney(prepayment);
                if (elTotal) elTotal.textContent = formatMoney(total);

                if (elBefore) elBefore.classList.add('d-none');
                if (elAfter) elAfter.classList.remove('d-none');

                if (hiddenCheckin) hiddenCheckin.value = p.ymdStart || '';
                if (hiddenCheckout) hiddenCheckout.value = p.ymdEnd || '';
                if (hiddenNights) hiddenNights.value = String(nights);

                if (hiddenPriceNightly) hiddenPriceNightly.value = basePrice.toFixed(2);
                if (hiddenPricePrepay) hiddenPricePrepay.value = prepayment.toFixed(2);
                if (hiddenPriceTotal) hiddenPriceTotal.value = total.toFixed(2);
            },
        },
    });

    // İlk yüklemede temiz state garanti
    resetState();
}
