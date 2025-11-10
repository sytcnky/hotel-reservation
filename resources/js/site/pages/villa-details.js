import flatpickr from 'flatpickr';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('checkin');
    if (!input) return;

    const price = parseInt(input.dataset.price);
    const disabledDates = JSON.parse(input.dataset.unavailable || '[]');

    flatpickr(input, {
        mode: "range",
        locale: Turkish,
        dateFormat: "d.m.Y",
        disable: disabledDates,
        minDate: "today",
        onChange: function(selectedDates) {
            const footer = document.querySelector('.card-footer');
            const warning = document.getElementById('min-nights-feedback');

            if (selectedDates.length < 2) {
                // Tek tarih seçildiğinde her şeyi gizle
                document.getElementById('price-before-selection').classList.remove('d-none');
                document.getElementById('price-after-selection').classList.add('d-none');
                footer.classList.remove('bg-primary');
                footer.classList.add('bg-secondary-subtle');
                warning.classList.add('d-none');
                return;
            }

            const [start, end] = selectedDates;
            const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

            if (nights < 3) {
                // Minimum gece sayısı sağlanmadı → uyarı göster
                document.getElementById('price-before-selection').classList.remove('d-none');
                document.getElementById('price-after-selection').classList.add('d-none');
                footer.classList.remove('bg-primary');
                footer.classList.add('bg-secondary-subtle');
                warning.classList.remove('d-none');
                return;
            }

            // ✅ Geçerli seçim → uyarıyı gizle, fiyatları hesapla
            warning.classList.add('d-none');

            const discounted = price * nights;
            const full = Math.round(discounted / 0.85);

            document.getElementById('price-multiplied').textContent = `${nights} × ${price.toLocaleString('tr-TR')}₺`;
            document.getElementById('price-total-original').textContent = `Toplam: ${full.toLocaleString('tr-TR')}₺`;
            document.getElementById('price-total-discounted').textContent = `Toplam: ${discounted.toLocaleString('tr-TR')}₺`;

            document.getElementById('price-before-selection').classList.add('d-none');
            document.getElementById('price-after-selection').classList.remove('d-none');

            footer.classList.remove('bg-secondary-subtle');
            footer.classList.add('bg-primary');

            // 📩 Gizli inputlara yaz
            document.getElementById('hidden-checkin').value = flatpickr.formatDate(start, 'Y-m-d');
            document.getElementById('hidden-checkout').value = flatpickr.formatDate(end, 'Y-m-d');
        }
    });
});
