// resources/js/pages/help.js
export function initHelpSearch() {
    const acc   = document.getElementById('faqAccordion');
    const input = document.getElementById('faqSearch');
    const empty = document.getElementById('faqEmpty');
    if (!acc || !input) return; // sadece yardım sayfasında çalış

    const items = Array.from(acc.querySelectorAll('.accordion-item'));
    const firstCollapse = items[0]?.querySelector('.accordion-collapse') || null;
    const firstBtn = items[0]?.querySelector('.accordion-button') || null;

    const setOpen = (item, open) => {
        const btn = item.querySelector('.accordion-button');
        const col = item.querySelector('.accordion-collapse');
        if (!btn || !col) return;
        if (open) {
            col.classList.add('show');
            btn.classList.remove('collapsed');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            col.classList.remove('show');
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        }
    };

    const filter = () => {
        const q = (input.value || '').trim().toLowerCase();
        let shown = 0;

        items.forEach(item => {
            const txt = item.textContent.toLowerCase();
            const match = q === '' || txt.includes(q);
            item.classList.toggle('d-none', !match);
            setOpen(item, match && q !== '');
            if (match) shown++;
        });

        if (empty) empty.classList.toggle('d-none', shown > 0);

        // arama boşken: hepsini kapat, ilk öğeyi açık bırak
        if (q === '') {
            items.forEach(it => setOpen(it, false));
            if (firstCollapse && firstBtn) {
                firstCollapse.classList.add('show');
                firstBtn.classList.remove('collapsed');
                firstBtn.setAttribute('aria-expanded', 'true');
                firstCollapse.closest('.accordion-item')?.classList.remove('d-none');
            }
        }
    };

    const debounce = (fn, ms = 120) => {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    };

    input.addEventListener('input', debounce(filter, 120));
    filter(); // initial state
}
