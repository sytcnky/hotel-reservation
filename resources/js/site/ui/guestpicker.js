export function initGuestPicker(container = document) {
    const pickers = container.querySelectorAll('.guest-picker-wrapper');
    if (!pickers.length) return;

    pickers.forEach((picker, index) => {
        const input = picker.querySelector('.guest-wrapper');
        const dropdown = picker.querySelector('.guest-dropdown');

        if (!input || !dropdown) return;

        const counts = {
            adult: 0,
            child: 0,
            infant: 0
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

            const totalGuests = counts.adult + counts.child;
            const event = new CustomEvent('guestCountChanged', { detail: { total: totalGuests } });
            document.dispatchEvent(event);
        }

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        dropdown.addEventListener('click', (e) => {
            const btn = e.target;
            if (btn.classList.contains('plus') || btn.classList.contains('minus')) {
                const type = btn.dataset.type;
                if (!type || !(type in counts)) return;

                if (btn.classList.contains('plus')) {
                    counts[type]++;
                } else {
                    const min = type === 'adult' ? 1 : 0;
                    if (counts[type] > min) counts[type]--;
                }

                updateDisplay();
            }
        });

        // Sayfa dışında bir yere tıklanınca kapanması için
        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        updateDisplay();
    });
}
