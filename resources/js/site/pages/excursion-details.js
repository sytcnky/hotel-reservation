import flatpickr from "flatpickr";
import { Turkish } from "flatpickr/dist/l10n/tr.js";
import { initGuestPicker } from "../ui/guestpicker.js";

export function initExcursionDetails() {
    initGuestPicker(document);

    const dateInput = document.getElementById('excursion-date');
    const priceOutput = document.getElementById('excursion-price-total');
    const guestInput = document.getElementById('guestInput');

    // Flatpickr başlat
    if (dateInput) {
        flatpickr(dateInput, {
            locale: Turkish,
            dateFormat: "d.m.Y",
            minDate: "today",
        });
    }

    // Fiyatı hesapla
    function calculateTotal() {
        if (!guestInput || !guestInput.dataset.prices) return;

        const prices = JSON.parse(guestInput.dataset.prices);
        const currency = guestInput.dataset.currency || 'TRY';

        const counts = {
            adult: parseInt(document.getElementById('adultCount')?.value || '0', 10),
            child: parseInt(document.getElementById('childCount')?.value || '0', 10),
            infant: parseInt(document.getElementById('infantCount')?.value || '0', 10),
        };

        let total = 0;
        for (const type in counts) {
            const count = counts[type];
            const price = prices?.[type]?.[currency];

            if (typeof price === 'undefined') continue;

            total += count * price;
        }

        if (priceOutput) {
            priceOutput.textContent = `${total.toLocaleString('tr-TR')}₺`;
        }
    }


    // Artırma/azaltma butonları
    window.increase = function (type) {
        const input = document.getElementById(`${type}Count`);
        const hidden = document.getElementById(`input${capitalize(type)}`);
        if (!input || !hidden) return;

        let val = parseInt(input.value || '0', 10);
        if (val < 10) {
            input.value = val + 1;
            hidden.value = val + 1;
            calculateTotal();
        }
    };

    window.decrease = function (type) {
        const input = document.getElementById(`${type}Count`);
        const hidden = document.getElementById(`input${capitalize(type)}`);
        if (!input || !hidden) return;

        let val = parseInt(input.value || '0', 10);
        const min = (type === 'adult') ? 1 : 0;
        if (val > min) {
            input.value = val - 1;
            hidden.value = val - 1;
            calculateTotal();
        }
    };

    // Sayfa açıldığında toplam fiyatı göster
    calculateTotal();

    // guestpicker.js bir değişiklik yayarsa (örn. dropdown kapatıldığında)
    document.addEventListener('guestCountChanged', calculateTotal);

    // Yardımcı
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}
