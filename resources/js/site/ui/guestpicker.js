// resources/js/ui/guestpicker.js

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

            if (counts.adult > 0) {
                parts.push(`${counts.adult} ${input.dataset.labelAdult}`);
            }
            if (counts.child > 0) {
                parts.push(`${counts.child} ${input.dataset.labelChild}`);
            }
            if (counts.infant > 0) {
                parts.push(`${counts.infant} ${input.dataset.labelInfant}`);
            }

            input.value = parts.length
                ? parts.join(', ')
                : input.placeholder;

            // Dropdown içindeki sayısal input'ları güncelle
            const adultInput  = dropdown.querySelector('input[data-type="adult"]');
            const childInput  = dropdown.querySelector('input[data-type="child"]');
            const infantInput = dropdown.querySelector('input[data-type="infant"]');

            if (adultInput)  adultInput.value  = counts.adult;
            if (childInput)  childInput.value  = counts.child;
            if (infantInput) infantInput.value = counts.infant;

            // Aynı picker içinde HIDDEN alanları (name="adults|children|infants") güncelle
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

            // Sayfa genelinde kullanılan bazı hidden ID'ler:
            // - Excursion: #inputAdults, #inputChildren, #inputInfants
            // - Hotel:    #adultsInput, #childrenInput
            const idMap = {
                adult: ['inputAdults', 'adultsInput'],
                child: ['inputChildren', 'childrenInput'],
                infant: ['inputInfants', 'infantsInput'],
            };

            Object.entries(idMap).forEach(([type, ids]) => {
                ids.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) el.value = String(counts[type]);
                });
            });

            // Toplam misafir sayısını global event ile yayınla
            const totalGuests = counts.adult + counts.child + counts.infant;
            const event = new CustomEvent('guestCountChanged', { detail: { total: totalGuests } });
            document.dispatchEvent(event);
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
