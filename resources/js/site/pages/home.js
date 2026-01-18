// resources/js/pages/home.js

export function initHome() {
    console.log('[Home] initHome() start');

    // --- helpers ---
    const safeInt = (v, fallback) => {
        const n = parseInt(String(v ?? ''), 10);
        return Number.isFinite(n) ? n : fallback;
    };

    const pxToNum = (v) => {
        const n = parseFloat(String(v ?? ''));
        return Number.isFinite(n) ? n : 0;
    };

    const section = document.getElementById('popular-hotels');
    console.log('[Home] section #popular-hotels', section);

    if (!section) {
        console.warn('[Home] ABORT: #popular-hotels not found');
        return;
    }

    console.log('[Home] section dataset', { ...section.dataset });

    // NOTE: .popular-hotels-carousel yok (kaldırdık). Sadece viewport/track üzerinden gidiyoruz.
    const viewport = section.querySelector('.popular-hotels-viewport');
    const track = section.querySelector('.popular-hotels-track');
    const slides = Array.from(section.querySelectorAll('.popular-hotels-page'));

    // Ok butonları iki yerde olabilir (desktop + mobile) => hepsine bağlan.
    const btnPrevAll = Array.from(section.querySelectorAll('.popular-hotels-prev'));
    const btnNextAll = Array.from(section.querySelectorAll('.popular-hotels-next'));

    console.log('[Home] nodes', {
        viewport,
        track,
        slidesCount: slides.length,
        btnPrevCount: btnPrevAll.length,
        btnNextCount: btnNextAll.length,
    });

    if (!viewport || !track) {
        console.warn('[Home] ABORT: viewport/track missing', { viewport, track });
        return;
    }

    if (slides.length === 0) {
        console.warn('[Home] ABORT: slides.length === 0');
        return;
    }

    if (btnPrevAll.length === 0 || btnNextAll.length === 0) {
        console.warn('[Home] ABORT: prev/next buttons missing', {
            btnPrevCount: btnPrevAll.length,
            btnNextCount: btnNextAll.length,
        });
        return;
    }

    let index = 0;

    const getPerView = () => {
        const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
        const raw = section.dataset.perView;
        const perView = isMobile ? 1 : safeInt(raw, 1);

        console.log('[Home] getPerView()', { isMobile, raw, perView });
        return Math.max(1, perView);
    };

    const getGap = () => {
        const cs = window.getComputedStyle(track);
        const gapStr = cs.gap || cs.columnGap || '0px';
        const gap = pxToNum(gapStr);

        console.log('[Home] getGap()', { gapStr, gap });
        return gap;
    };

    const getSlideWidth = () => {
        const rect = slides[0].getBoundingClientRect();
        console.log('[Home] getSlideWidth()', { width: rect.width, rect });
        return rect.width;
    };

    const getMaxIndex = () => Math.max(0, slides.length - 1);

    const clampIndex = () => {
        const before = index;
        const maxIndex = getMaxIndex();

        if (index < 0) index = 0;
        if (index > maxIndex) index = maxIndex;

        console.log('[Home] clampIndex()', { before, after: index, maxIndex });
    };

    const setButtonsState = () => {
        const maxIndex = getMaxIndex();

        const prevDisabled = index === 0;
        const nextDisabled = index === maxIndex;

        // iki set buton (desktop/mobile) birlikte güncellensin
        btnPrevAll.forEach((btn) => {
            btn.toggleAttribute('disabled', prevDisabled);
            btn.classList.toggle('text-secondary', prevDisabled);
        });

        btnNextAll.forEach((btn) => {
            btn.toggleAttribute('disabled', nextDisabled);
            btn.classList.toggle('text-secondary', nextDisabled);
        });

        console.log('[Home] setButtonsState()', { index, maxIndex, prevDisabled, nextDisabled });
    };

    const applyTransform = () => {
        const slideWidth = getSlideWidth();
        const gap = getGap();
        const x = (slideWidth + gap) * index;

        track.style.transform = `translateX(${-x}px)`;

        console.log('[Home] applyTransform()', {
            index,
            slideWidth,
            gap,
            x,
            transform: track.style.transform,
        });
    };

    const update = (reason = 'update') => {
        console.groupCollapsed(`[Home] update() reason=${reason}`);
        console.log('[Home] before', { index });

        clampIndex();
        applyTransform();
        setButtonsState();

        console.log('[Home] after', { index });
        console.groupEnd();
    };

    const prev = () => {
        const before = index;
        index -= 1;
        console.log('[Home] prev()', { before, after: index });
        update('prev_click');
    };

    const next = () => {
        const before = index;
        index += 1;
        console.log('[Home] next()', { before, after: index });
        update('next_click');
    };

    // desktop/mobile tüm oklar
    btnPrevAll.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            console.groupCollapsed('[Home] CLICK prev');
            console.log('[Home] event', e);
            prev();
            console.groupEnd();
        });
    });

    btnNextAll.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            console.groupCollapsed('[Home] CLICK next');
            console.log('[Home] event', e);
            next();
            console.groupEnd();
        });
    });

    // (iyileştirme) track width değişebilir: resize + font load durumlarında update
    window.addEventListener('resize', () => {
        console.log('[Home] window resize');
        update('resize');
    });

    // (iyileştirme) ilk render sonrası layout otursun diye bir kez daha
    requestAnimationFrame(() => update('raf'));

    // --- initial ---
    update('init');

    console.log('[Home] initHome() done');
}
