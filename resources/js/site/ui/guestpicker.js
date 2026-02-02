// resources/js/ui/guestpicker.js

export function initGuestPicker(container = document) {
    const pickers = container.querySelectorAll('.guest-picker-wrapper');
    if (!pickers.length) return;

    pickers.forEach((picker) => {
        const input = picker.querySelector('.guest-wrapper');
        const dropdown = picker.querySelector('.guest-dropdown');
        if (!input || !dropdown) return;

        const getInitial = (type, fallback) => {
            const el = dropdown.querySelector(`input[data-type="${type}"]`);
            if (!el) return fallback;

            const v = parseInt(el.value, 10);
            return Number.isFinite(v) && v >= 0 ? v : fallback;
        };

        // No assumptions: defaults are 0; server/blade provides real initial values when needed.
        const counts = {
            adult: getInitial('adult', 0),
            child: getInitial('child', 0),
            infant: getInitial('infant', 0),
        };

        function updateDisplay() {
            const parts = [];

            const adultLabel = input.dataset.labelAdult || '';
            const childLabel = input.dataset.labelChild || '';
            const infantLabel = input.dataset.labelInfant || '';

            if (counts.adult > 0) parts.push(`${counts.adult} ${adultLabel}`);
            if (counts.child > 0) parts.push(`${counts.child} ${childLabel}`);
            if (counts.infant > 0) parts.push(`${counts.infant} ${infantLabel}`);

            const placeholder =
                input.dataset.placeholder ||
                input.getAttribute('placeholder') ||
                '';

            input.value = parts.length ? parts.join(', ') : placeholder;

            // Dropdown içindeki sayısal input'ları güncelle
            const adultInput = dropdown.querySelector('input[data-type="adult"]');
            const childInput = dropdown.querySelector('input[data-type="child"]');
            const infantInput = dropdown.querySelector('input[data-type="infant"]');

            if (adultInput) adultInput.value = String(counts.adult);
            if (childInput) childInput.value = String(counts.child);
            if (infantInput) infantInput.value = String(counts.infant);

            // Hidden alanları sadece bu picker içinde güncelle
            const nameMap = {
                adult: 'adults',
                child: 'children',
                infant: 'infants',
            };

            Object.entries(nameMap).forEach(([type, name]) => {
                const hiddenInputs = picker.querySelectorAll(`input[type="hidden"][name="${name}"]`);
                hiddenInputs.forEach((el) => {
                    el.value = String(counts[type]);
                });
            });

            // Toplam misafir sayısını global event ile yayınla
            const totalGuests = counts.adult + counts.child + counts.infant;
            document.dispatchEvent(
                new CustomEvent('guestCountChanged', { detail: { total: totalGuests } })
            );
        }

        // Input'a tıklayınca aç/kapat
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Artı / eksi butonları
        dropdown.addEventListener('click', (e) => {
            const btn = e.target;
            if (!btn.classList.contains('plus') && !btn.classList.contains('minus')) return;

            const type = btn.dataset.type;
            if (!type || !(type in counts)) return;

            if (btn.classList.contains('plus')) {
                counts[type]++;
            } else {
                // Only enforce min on user decrement action
                const min = type === 'adult' ? 1 : 0;
                if (counts[type] > min) counts[type]--;
            }

            updateDisplay();
        });

        // Picker dışına tıklayınca dropdown kapanır
        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // İlk yüklemede server'dan gelen değerlerle göster
        updateDisplay();
    });
}
