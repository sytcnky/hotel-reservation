document.addEventListener('DOMContentLoaded', () => {

    // 👉 "Tümünü Göster/Gizle" butonu (Tesis Olanakları)
    const toggleFacilitiesBtn = document.getElementById('toggleFacilitiesBtn');
    if (toggleFacilitiesBtn) {
        let open = false;
        toggleFacilitiesBtn.addEventListener('click', () => {
            document.querySelectorAll('.extra-facility').forEach(el => el.classList.toggle('d-none'));
            open = !open;
            toggleFacilitiesBtn.textContent = open ? 'Daha Az Göster' : 'Tümünü Göster';
        });
    }

    // 👉 "Tümünü Göster/Gizle" butonu (Çocuk Hizmetleri)
    const toggleChildFeaturesBtn = document.getElementById('toggleChildFeaturesBtn');
    if (toggleChildFeaturesBtn) {
        let open = false;
        toggleChildFeaturesBtn.addEventListener('click', () => {
            document.querySelectorAll('.extra-child-feature').forEach(el => el.classList.toggle('d-none'));
            open = !open;
            toggleChildFeaturesBtn.textContent = open ? 'Daha Az Göster' : 'Tümünü Göster';
        });
    }

    // 👉 Filtre formunu gizle/göster
    const toggleFilterBtn = document.getElementById('toggleFilterBtn');
    const filterForm = document.querySelector('form[action*="hotels"]');
    const filterCol = document.getElementById('filterCol');
    const listingCol = document.getElementById('listingCol');

    if (toggleFilterBtn && filterForm && filterCol && listingCol) {
        // Sayfa yüklendiğinde filtre gizliyse otel listesi genişletilsin
        if (filterForm.classList.contains('d-none')) {
            listingCol.classList.remove('col-xl-9');
            listingCol.classList.add('col-xl-12');
            toggleFilterBtn.classList.add('active');
            toggleFilterBtn.setAttribute('aria-expanded', 'false');
        }

        toggleFilterBtn.addEventListener('click', () => {
            const isHidden = filterForm.classList.toggle('d-none');
            toggleFilterBtn.classList.toggle('active', isHidden);
            toggleFilterBtn.setAttribute('aria-expanded', String(!isHidden));

            if (isHidden) {
                listingCol.classList.remove('col-xl-9');
                listingCol.classList.add('col-xl-12');
            } else {
                listingCol.classList.remove('col-xl-12');
                listingCol.classList.add('col-xl-9');
            }
        });
    }

});
