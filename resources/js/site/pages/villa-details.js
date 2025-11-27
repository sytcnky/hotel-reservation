// resources/js/pages/villa-details.js
import flatpickr from 'flatpickr';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

export function initVillaDetails() {
    const box       = document.getElementById('villa-price-box');
    const dateInput = document.getElementById('checkin');

    if (!box || !dateInput) {
        return;
    }

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

    const hiddenCheckin        = document.getElementById('hidden-checkin');
    const hiddenCheckout       = document.getElementById('hidden-checkout');
    const hiddenNights         = document.getElementById('villa-nights');
    const hiddenPriceNightly   = document.getElementById('villa-price-nightly');
    const hiddenPricePrepay    = document.getElementById('villa-price-prepayment');
    const hiddenPriceTotal     = document.getElementById('villa-price-total');

    function formatMoney(value) {
        return new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value) + ' ' + currency;
    }

    function resetState() {
        if (elBefore) elBefore.classList.remove('d-none');
        if (elAfter)  elAfter.classList.add('d-none');

        if (elMinNights) elMinNights.classList.add('d-none');
        if (elMaxNights) elMaxNights.classList.add('d-none');

        if (hiddenCheckin)  hiddenCheckin.value = '';
        if (hiddenCheckout) hiddenCheckout.value = '';
        if (hiddenNights)   hiddenNights.value = '';
        if (hiddenPriceNightly) hiddenPriceNightly.value = '';
        if (hiddenPricePrepay)  hiddenPricePrepay.value  = '';
        if (hiddenPriceTotal)   hiddenPriceTotal.value   = '';
    }

    flatpickr(dateInput, {
        mode: 'range',
        locale: Turkish,
        dateFormat: 'd.m.Y',
        minDate: 'today',
        onChange(selectedDates) {
            if (selectedDates.length < 2) {
                resetState();
                return;
            }

            const [start, end] = selectedDates;
            const nights = Math.ceil((end - start) / 86400000);

            // Min / max kontrol
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
                // Koşullar sağlanmadı → state’i sıfırla ama tarihleri bırak
                if (elBefore) elBefore.classList.remove('d-none');
                if (elAfter)  elAfter.classList.add('d-none');

                if (hiddenCheckin)  hiddenCheckin.value = '';
                if (hiddenCheckout) hiddenCheckout.value = '';
                if (hiddenNights)   hiddenNights.value = '';
                if (hiddenPriceNightly) hiddenPriceNightly.value = '';
                if (hiddenPricePrepay)  hiddenPricePrepay.value  = '';
                if (hiddenPriceTotal)   hiddenPriceTotal.value   = '';
                return;
            }

            const total      = basePrice * nights;
            const prepayment = Math.round(total * (prepayRate / 100));

            if (elNightly)    elNightly.textContent    = formatMoney(basePrice);
            if (elNights)     elNights.textContent     = String(nights);
            if (elPrepayment) elPrepayment.textContent = formatMoney(prepayment);
            if (elTotal)      elTotal.textContent      = formatMoney(total);

            if (elBefore) elBefore.classList.add('d-none');
            if (elAfter)  elAfter.classList.remove('d-none');

            if (hiddenCheckin) {
                hiddenCheckin.value = start.toISOString().slice(0, 10);
            }
            if (hiddenCheckout) {
                hiddenCheckout.value = end.toISOString().slice(0, 10);
            }
            if (hiddenNights) {
                hiddenNights.value = String(nights);
            }
            if (hiddenPriceNightly) {
                hiddenPriceNightly.value = basePrice.toFixed(2);
            }
            if (hiddenPricePrepay) {
                hiddenPricePrepay.value = prepayment.toFixed(2);
            }
            if (hiddenPriceTotal) {
                hiddenPriceTotal.value = total.toFixed(2);
            }
        },
    });
}
