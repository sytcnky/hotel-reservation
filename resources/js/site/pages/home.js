// resources/js/pages/home.js
import { initDatePicker } from '../ui/date-picker';

export function initHome() {
    initPopularHotels();
    initHomeHotelTab();
    initHomeTransferTab();
}

/* =====================================================
   Helpers (Flatpickr + is-invalid)
   - Flatpickr altInput kullanıyorsa, is-invalid hem input'tan
     hem altInput'tan temizlenir.
===================================================== */
function clearDateInvalid(input) {
    if (!input) return;

    input.classList.remove('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.remove('is-invalid');
    }
}

function markDateInvalid(input) {
    if (!input) return;

    input.classList.add('is-invalid');

    const fp = input._flatpickr;
    if (fp && fp.altInput) {
        fp.altInput.classList.add('is-invalid');
    }
}

function bindDateInvalidClear(input) {
    if (!input) return;

    const fp = input._flatpickr;
    const vis = fp && fp.altInput ? fp.altInput : input;

    const handler = () => {
        const val = (vis.value || '').trim();
        if (val) {
            clearDateInvalid(input);
        }
    };

    vis.addEventListener('input', handler);
    vis.addEventListener('change', handler);

    if (fp) {
        fp.config.onChange = (fp.config.onChange || []).concat(() => clearDateInvalid(input));
    }
}

/* =====================================================
   POPULAR HOTELS CAROUSEL
===================================================== */
function initPopularHotels() {
    const section = document.getElementById('popular-hotels');
    if (!section) return;

    const viewport = section.querySelector('.popular-hotels-viewport');
    const track = section.querySelector('.popular-hotels-track');
    const slides = Array.from(section.querySelectorAll('.popular-hotels-page'));
    const btnPrevAll = Array.from(section.querySelectorAll('.popular-hotels-prev'));
    const btnNextAll = Array.from(section.querySelectorAll('.popular-hotels-next'));

    if (!viewport || !track || slides.length === 0) return;

    let index = 0;

    const getGap = () => {
        const cs = window.getComputedStyle(track);
        return parseFloat(cs.gap || cs.columnGap || '0') || 0;
    };

    const getSlideWidth = () => slides[0].getBoundingClientRect().width;

    const clampIndex = () => {
        const max = Math.max(0, slides.length - 1);
        if (index < 0) index = 0;
        if (index > max) index = max;
    };

    const updateButtons = () => {
        const max = Math.max(0, slides.length - 1);
        btnPrevAll.forEach(b => b && (b.disabled = index === 0));
        btnNextAll.forEach(b => b && (b.disabled = index === max));
    };

    const applyTransform = () => {
        const x = (getSlideWidth() + getGap()) * index;
        track.style.transform = `translateX(${-x}px)`;
    };

    const update = () => {
        clampIndex();
        applyTransform();
        updateButtons();
    };

    btnPrevAll.forEach(btn => btn && btn.addEventListener('click', () => {
        index--;
        update();
    }));

    btnNextAll.forEach(btn => btn && btn.addEventListener('click', () => {
        index++;
        update();
    }));

    window.addEventListener('resize', update);
    requestAnimationFrame(update);
}

/* =====================================================
   HOTEL TAB
===================================================== */
function initHomeHotelTab() {
    const form = document.getElementById('homeHotelSearchForm');
    if (!form) return;

    const dateInput = document.getElementById('home_hotel_checkin');
    const guestsTotalInput = form.querySelector('[data-home-hotel-guests-total]');

    if (dateInput) {
        initDatePicker({
            el: dateInput,
            contract: 'hotel_listing_range',
            locale: document.documentElement.lang,
        });

        // flatpickr altInput için invalid temizleme bağla
        bindDateInvalidClear(dateInput);
    }

    document.addEventListener('guestCountChanged', (e) => {
        const total = e?.detail?.total ?? 0;
        if (guestsTotalInput) {
            guestsTotalInput.value = String(Math.max(1, total));
        }
    });

    form.addEventListener('submit', (e) => {
        let valid = true;

        const visible = dateInput?._flatpickr?.altInput || dateInput;
        const val = (visible?.value || '').trim();

        if (!val) {
            markDateInvalid(dateInput);
            valid = false;
        } else {
            clearDateInvalid(dateInput);
        }

        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
}

/* =====================================================
   TRANSFER TAB
===================================================== */
function initHomeTransferTab() {
    const form = document.getElementById('homeTransferSearchForm');
    if (!form) return;

    const dep = document.getElementById('home_departure_date');
    const ret = document.getElementById('home_return_date');
    const oneway = document.getElementById('home_oneway');
    const roundtrip = document.getElementById('home_roundtrip');
    const retWrap = document.getElementById('homeReturnDateWrapper');
    const fromSelect = document.getElementById('home_from_location_id');
    const toSelect = document.getElementById('home_to_location_id');

    if (dep) {
        initDatePicker({
            el: dep,
            contract: 'transfer_single_ymd_alt',
            locale: document.documentElement.lang,
        });
        bindDateInvalidClear(dep);
    }

    if (ret) {
        initDatePicker({
            el: ret,
            contract: 'transfer_single_ymd_alt',
            locale: document.documentElement.lang,
        });
        bindDateInvalidClear(ret);
    }

    if (oneway && roundtrip && retWrap && ret) {
        const sync = () => {
            const show = roundtrip.checked;
            retWrap.classList.toggle('d-none', !show);

            // roundtrip ise return_date zorunlu olsun
            ret.required = show;

            if (!show) {
                ret.value = '';
                clearDateInvalid(ret);
            }
        };

        oneway.addEventListener('change', sync);
        roundtrip.addEventListener('change', sync);
        sync();
    }

    // From/To: aynı lokasyonu engelle
    if (fromSelect && toSelect) {
        const originalOptions = Array.from(toSelect.options).map(o => ({
            value: o.value,
            text: o.textContent,
        }));

        const rebuild = () => {
            const fromVal = fromSelect.value;
            const prev = toSelect.value;

            toSelect.innerHTML = '';

            originalOptions.forEach(opt => {
                if (opt.value === '' || opt.value !== fromVal) {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;

                    if (opt.value === prev && opt.value !== fromVal) {
                        o.selected = true;
                    }

                    toSelect.appendChild(o);
                }
            });

            if (toSelect.value === fromVal) {
                toSelect.value = '';
            }
        };

        rebuild();
        fromSelect.addEventListener('change', rebuild);
    }

    form.addEventListener('submit', (e) => {
        let valid = true;

        if (!fromSelect || !fromSelect.value) {
            fromSelect?.classList.add('is-invalid');
            valid = false;
        } else {
            fromSelect.classList.remove('is-invalid');
        }

        if (!toSelect || !toSelect.value) {
            toSelect?.classList.add('is-invalid');
            valid = false;
        } else {
            toSelect.classList.remove('is-invalid');
        }

        const depVisible = dep?._flatpickr?.altInput || dep;
        const depVal = (depVisible?.value || '').trim();

        if (!depVal) {
            markDateInvalid(dep);
            valid = false;
        } else {
            clearDateInvalid(dep);
        }

        if (roundtrip?.checked) {
            const retVisible = ret?._flatpickr?.altInput || ret;
            const retVal = (retVisible?.value || '').trim();

            if (!retVal) {
                markDateInvalid(ret);
                valid = false;
            } else {
                clearDateInvalid(ret);
            }
        } else {
            clearDateInvalid(ret);
        }

        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    fromSelect?.addEventListener('change', () => fromSelect.classList.remove('is-invalid'));
    toSelect?.addEventListener('change', () => toSelect.classList.remove('is-invalid'));
}
