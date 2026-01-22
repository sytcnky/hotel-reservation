// resources/js/pages/excursion-details.js
import { initDatePicker } from '../ui/date-picker';

export function initExcursionDetails() {
    const form = document.getElementById('excursionForm');
    const dateInput   = document.getElementById('excursion-date');
    const guestInput  = document.getElementById('guestInput');
    const priceOutput = document.getElementById('excursion-price-total');

    const hiddenAdults   = document.getElementById('inputAdults');
    const hiddenChildren = document.getElementById('inputChildren');
    const hiddenInfants  = document.getElementById('inputInfants');

    if (dateInput) {
        initDatePicker({
            el: dateInput,
            contract: 'excursion_single_ymd_alt',
            locale: document.documentElement.lang,
        });
    }

    if (form && dateInput) {
        form.addEventListener('submit', function (event) {
            const value = (dateInput.value || '').trim();

            if (!value) {
                event.preventDefault();
                event.stopPropagation();

                dateInput.classList.add('is-invalid');
                return;
            }

            dateInput.classList.remove('is-invalid');
        });

        ['input', 'change'].forEach(function (evt) {
            dateInput.addEventListener(evt, function () {
                if ((dateInput.value || '').trim() !== '') {
                    dateInput.classList.remove('is-invalid');
                }
            });
        });
    }

    function readCounts() {
        const dropdown = guestInput
            ? guestInput.closest('.guest-picker-wrapper')?.querySelector('.guest-dropdown')
            : null;

        const aEl = dropdown?.querySelector('input[data-type="adult"]');
        const cEl = dropdown?.querySelector('input[data-type="child"]');
        const iEl = dropdown?.querySelector('input[data-type="infant"]');

        const adults   = parseInt(aEl?.value ?? hiddenAdults?.value ?? '0', 10) || 0;
        const children = parseInt(cEl?.value ?? hiddenChildren?.value ?? '0', 10) || 0;
        const infants  = parseInt(iEl?.value ?? hiddenInfants?.value ?? '0', 10) || 0;

        if (hiddenAdults)   hiddenAdults.value   = String(adults);
        if (hiddenChildren) hiddenChildren.value = String(children);
        if (hiddenInfants)  hiddenInfants.value  = String(infants);

        return { adults, children, infants };
    }

    function updateGuestDisplay() {
        if (!guestInput) return;

        const { adults, children, infants } = readCounts();
        const parts = [];

        const adultLabel  = guestInput.dataset.labelAdult || '';
        const childLabel  = guestInput.dataset.labelChild || '';
        const infantLabel = guestInput.dataset.labelInfant || '';
        const placeholder = guestInput.dataset.placeholder || guestInput.getAttribute('placeholder') || '';

        if (adults > 0)   parts.push(adults + ' ' + adultLabel);
        if (children > 0) parts.push(children + ' ' + childLabel);
        if (infants > 0)  parts.push(infants + ' ' + infantLabel);

        guestInput.value = parts.length ? parts.join(', ') : placeholder;
    }

    function calculateTotal() {
        if (!guestInput || !guestInput.dataset.prices) return;

        let prices;
        try {
            prices = JSON.parse(guestInput.dataset.prices);
        } catch {
            return;
        }

        const currencyRaw = (guestInput.dataset.currency || '').trim();
        const currency = currencyRaw ? currencyRaw.toUpperCase() : '';
        if (!currency) {
            if (priceOutput) priceOutput.textContent = '—';
            return;
        }

        const cfg = prices && prices[currency] ? prices[currency] : null;
        if (!cfg) {
            if (priceOutput) priceOutput.textContent = '—';
            return;
        }

        const { adults, children, infants } = readCounts();

        let total = 0;
        total += adults   * Number(cfg.adult  ?? 0);
        total += children * Number(cfg.child  ?? 0);
        total += infants  * Number(cfg.infant ?? 0);

        const uiLocale = document.documentElement.lang;

        if (priceOutput) {
            priceOutput.textContent =
                total > 0
                    ? `${total.toLocaleString(uiLocale)} ${currency}`
                    : '—';
        }
    }

    document.addEventListener('guestCountChanged', calculateTotal);
    calculateTotal();

    document.addEventListener('guestCountChanged', calculateTotal);
    calculateTotal();

    document.addEventListener('guestCountChanged', updateGuestDisplay);
    updateGuestDisplay();
}
