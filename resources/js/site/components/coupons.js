// resources/js/components/coupons.js
(() => {
    const carousels = document.querySelectorAll('[data-coupon-carousel]');
    if (!carousels.length) return;

    carousels.forEach(root => {
        const track = root.querySelector('.coupon-track');
        const prevBtn = root.querySelector('.coupon-prev');
        const nextBtn = root.querySelector('.coupon-next');
        if (!track || !prevBtn || !nextBtn) return;

        let index = 0;
        const items = Array.from(track.children);

        const visibleCount = () => {
            const w = root.clientWidth;
            if (w >= 992) return 3;   // lg+
            if (w >= 768) return 2;   // md
            return 1;                 // sm
        };

        const update = () => {
            const vis = visibleCount();
            const maxIndex = Math.max(0, items.length - vis);
            index = Math.min(index, maxIndex);

            // kart genişliği = ilk kartın genişliği
            const card = items[0];
            const cardW = card ? card.getBoundingClientRect().width : 0;
            const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 0) || 12;

            const offset = (cardW + gap) * index * -1;
            track.style.transform = `translateX(${offset}px)`;
            track.style.transition = 'transform 300ms ease';

            // okların durumu
            prevBtn.disabled = (index <= 0);
            nextBtn.disabled = (index >= maxIndex);

            // 3’ten azsa okları gizle
            const shouldShowArrows = items.length > vis;
            prevBtn.parentElement.style.visibility = shouldShowArrows ? 'visible' : 'hidden';
        };

        prevBtn.addEventListener('click', () => { index = Math.max(0, index - 1); update(); });
        nextBtn.addEventListener('click', () => { index = index + 1; update(); });

        // Resize’ı dinle
        let rAF;
        const onResize = () => {
            cancelAnimationFrame(rAF);
            rAF = requestAnimationFrame(update);
        };
        window.addEventListener('resize', onResize);

        // İlk çizim
        update();
    });
})();
