// resources/js/pages/excursion-details.js
import { initDatePicker } from '../ui/date-picker';

export function initExcursionDetails() {
    const form = document.getElementById('excursionForm');
    const dateInput   = document.getElementById('excursion-date');
    const guestInput  = document.getElementById('guestInput');
    const priceOutput = document.getElementById('excursion-price-total');
    const totalInput  = document.getElementById('inputTotal');

    const hiddenAdults   = document.getElementById('inputAdults');
    const hiddenChildren = document.getElementById('inputChildren');
    const hiddenInfants  = document.getElementById('inputInfants');

    if (dateInput) {
        initDatePicker({
            el: dateInput,
            contract: 'excursion_single_dmy',
            locale: document.documentElement.lang,
        });
    }

    // --- SUBMIT: tarih zorunluluğu (inline script taşındı) ---
    if (form && dateInput) {
        form.addEventListener('submit', function (event) {
            const value = (dateInput.value || '').trim();

            if (!value) {
                event.preventDefault();
                event.stopPropagation();

                dateInput.classList.add('is-invalid');
                form.classList.add('was-validated');
                return;
            }

            dateInput.classList.remove('is-invalid');
            form.classList.add('was-validated');
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

    function calculateTotal() {
        if (!guestInput || !guestInput.dataset.prices) return;

        let prices;
        try {
            prices = JSON.parse(guestInput.dataset.prices);
        } catch {
            return;
        }

        const currency = (guestInput.dataset.currency || 'TRY').toUpperCase();
        const cfg = prices && prices[currency] ? prices[currency] : null;
        if (!cfg) return;

        const { adults, children, infants } = readCounts();

        let total = 0;
        total += adults   * Number(cfg.adult  ?? 0);
        total += children * Number(cfg.child  ?? 0);
        total += infants  * Number(cfg.infant ?? 0);

        if (priceOutput) {
            priceOutput.textContent =
                total > 0
                    ? `${total.toLocaleString('tr-TR')} ${currency}`
                    : '—';
        }

        if (totalInput) {
            totalInput.value = String(total);
        }
    }

    document.addEventListener('guestCountChanged', calculateTotal);
    calculateTotal();
}
