export function initGuestPicker(container = document) {
    const pickers = container.querySelectorAll('.guest-picker-wrapper');
    if (!pickers.length) return;

    pickers.forEach((picker) => {
        const input = picker.querySelector('.guest-wrapper');
        const dropdown = picker.querySelector('.guest-dropdown');
        if (!input || !dropdown) return;

        // Mevcut input değerlerinden initial sayıları oku
        const getInitial = (type, fallback) => {
            const el = dropdown.querySelector(`input[data-type="${type}"]`);
            if (!el) return fallback;
            const v = parseInt(el.value, 10);
            return Number.isFinite(v) && v >= 0 ? v : fallback;
        };

        const counts = {
            adult: getInitial('adult', 2),   // varsayılan 2 yetişkin
            child: getInitial('child', 0),
            infant: getInitial('infant', 0),
        };

        function updateDisplay() {
            const parts = [];
            if (counts.adult > 0) parts.push(`${counts.adult} Yetişkin`);
            if (counts.child > 0) parts.push(`${counts.child} Çocuk`);
            if (counts.infant > 0) parts.push(`${counts.infant} Bebek`);

            input.value = parts.length > 0 ? parts.join(', ') : 'Kişi sayısı seçin';

            const adultInput = dropdown.querySelector('input[data-type="adult"]');
            const childInput = dropdown.querySelector('input[data-type="child"]');
            const infantInput = dropdown.querySelector('input[data-type="infant"]');

            if (adultInput) adultInput.value = counts.adult;
            if (childInput) childInput.value = counts.child;
            if (infantInput) infantInput.value = counts.infant;

            const totalGuests = counts.adult + counts.child + counts.infant;
            const event = new CustomEvent('guestCountChanged', { detail: { total: totalGuests } });
            document.dispatchEvent(event);
        }

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        dropdown.addEventListener('click', (e) => {
            const btn = e.target;
            if (!btn.classList.contains('plus') && !btn.classList.contains('minus')) return;

            const type = btn.dataset.type;
            if (!type || !(type in counts)) return;

            if (btn.classList.contains('plus')) {
                counts[type]++;
            } else {
                const min = type === 'adult' ? 1 : 0;
                if (counts[type] > min) counts[type]--;
            }

            updateDisplay();
        });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // İlk yüklemede server'dan gelen değerlerle göster
        updateDisplay();
    });
}
