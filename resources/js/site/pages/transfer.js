// resources/js/pages/transfer.js
import { initDatePicker } from '../ui/date-picker';

export function initTransferForm() {
    // -------------------------------------------------
    // Element refs (search)
    // -------------------------------------------------
    const searchForm = document.getElementById('transferSearchForm');

    const fromSelect = document.getElementById('from_location_id');
    const toSelect = document.getElementById('to_location_id');

    const oneway = document.getElementById('oneway');
    const roundtrip = document.getElementById('roundtrip');

    const returnDateWrapper = document.getElementById('returnDateWrapper');
    const departureInput = document.getElementById('departure_date');
    const returnInput = document.getElementById('return_date');

    const guestInputVisible = document.getElementById('guestInput');

    // -------------------------------------------------
    // 1) Nereden -> Nereye aynı lokasyonu engelle
    // -------------------------------------------------
    if (fromSelect && toSelect) {
        const originalToOptions = Array.from(toSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        function rebuildToOptions() {
            const fromVal = fromSelect.value;
            const prevToVal = toSelect.value;

            toSelect.innerHTML = '';
            originalToOptions.forEach(opt => {
                if (opt.value === '' || opt.value !== fromVal) {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.text;
                    if (opt.value === prevToVal && opt.value !== fromVal) o.selected = true;
                    toSelect.appendChild(o);
                }
            });

            if (!toSelect.value) {
                const first = toSelect.querySelector('option[value=""]') || toSelect.options[0];
                if (first) first.selected = true;
            }
        }

        rebuildToOptions();

        fromSelect.addEventListener('change', function () {
            rebuildToOptions();
            fromSelect.classList.remove('is-invalid');
        });

        toSelect.addEventListener('change', function () {
            toSelect.classList.remove('is-invalid');
        });
    }

    // -------------------------------------------------
    // 2) Roundtrip toggle + return required senkronu
    // -------------------------------------------------
    if (oneway && roundtrip && returnDateWrapper && returnInput) {
        const toggleReturnDate = () => {
            if (roundtrip.checked) {
                returnDateWrapper.classList.remove('d-none');
            } else {
                returnDateWrapper.classList.add('d-none');
                returnInput.value = ''; // dönüş tarihi sıfırlanır
            }
        };

        oneway.addEventListener('change', toggleReturnDate);
        roundtrip.addEventListener('change', toggleReturnDate);
        requestAnimationFrame(toggleReturnDate);

        const syncReturnRequired = () => {
            returnInput.required = !!roundtrip.checked;
            returnInput.classList.remove('is-invalid');
        };
        syncReturnRequired();
        oneway.addEventListener('change', syncReturnRequired);
        roundtrip.addEventListener('change', syncReturnRequired);
    }

    // -------------------------------------------------
    // 3) Date pickers (UI d.m.Y, submit Y-m-d)
    // -------------------------------------------------
    if (departureInput) {
        const locale = document.documentElement.lang;

        initDatePicker({
            el: departureInput,
            contract: 'transfer_single_ymd_alt',
            locale,
            hooks: {
                onValidChange: (p) => {
                    // Departure değişince return minDate'i native min ile zorla (flatpickr'e dokunmadan)
                    if (returnInput && p?.ymd) {
                        returnInput.setAttribute('min', p.ymd);
                    }
                },
            },
        });

        departureInput.addEventListener('input', () => {
            if (departureInput.value.trim()) departureInput.classList.remove('is-invalid');
        });
    }

    if (returnInput) {
        initDatePicker({
            el: returnInput,
            contract: 'transfer_single_ymd_alt',
            locale: document.documentElement.lang,
        });

        returnInput.addEventListener('input', () => {
            if (returnInput.value.trim()) returnInput.classList.remove('is-invalid');
        });
    }

    // -------------------------------------------------
    // 4) GuestPicker değişimi (guestpicker.js tetikler)
    // -------------------------------------------------
    if (guestInputVisible) {
        document.addEventListener('guestCountChanged', function (e) {
            const total = e.detail && typeof e.detail.total === 'number' ? e.detail.total : 0;
            if (total > 0) guestInputVisible.classList.remove('is-invalid');
        });
    }

    // -------------------------------------------------
    // 5) Search form validation (client-side)
    // -------------------------------------------------
    if (searchForm) {
        searchForm.addEventListener('submit', function (event) {
            let valid = true;

            // Direction
            const dirInputs = searchForm.querySelectorAll('input[name="direction"]');
            const dirChecked = Array.from(dirInputs).some(i => i.checked);
            if (!dirChecked) valid = false;

            // From / To
            if (fromSelect && !fromSelect.value) { fromSelect.classList.add('is-invalid'); valid = false; }
            if (toSelect && !toSelect.value) { toSelect.classList.add('is-invalid'); valid = false; }

            // Dates
            const dep = departureInput;
            const ret = returnInput;

            if (!dep || !dep.value.trim()) {
                dep?.classList.add('is-invalid');
                valid = false;
            }

            if (roundtrip && roundtrip.checked) {
                if (!ret || !ret.value.trim()) {
                    ret?.classList.add('is-invalid');
                    valid = false;
                }
            }

            // Guests (min 1 adult, total > 0)
            const adults = parseInt(searchForm.querySelector('input[name="adults"]')?.value || '0', 10);
            const children = parseInt(searchForm.querySelector('input[name="children"]')?.value || '0', 10);
            const infants = parseInt(searchForm.querySelector('input[name="infants"]')?.value || '0', 10);
            const total = adults + children + infants;
            const guestValid = adults >= 1 && total > 0;

            if (!guestValid && guestInputVisible) {
                guestInputVisible.classList.add('is-invalid');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    // -------------------------------------------------
    // 6) Booking form: Radio'ya göre (Saat / Uçuş No) tek alan aktif
    // -------------------------------------------------
    const bookForm = document.getElementById('transferBookForm');
    if (bookForm) {
        const pairError = document.getElementById('bookPairError');

        // Outbound
        const outTime = document.getElementById('pickup_time_outbound');
        const outFlight = document.getElementById('flight_number_outbound');
        const outTimeWrap = document.getElementById('outbound_time_wrapper');
        const outFlightWrap = document.getElementById('outbound_flight_wrapper');
        const outRadios = bookForm.querySelectorAll('input[name="outbound_input_type"]');

        // Return (roundtrip ise var)
        const retTime = document.getElementById('pickup_time_return');
        const retFlight = document.getElementById('flight_number_return');
        const retTimeWrap = document.getElementById('return_time_wrapper');
        const retFlightWrap = document.getElementById('return_flight_wrapper');
        const retRadios = bookForm.querySelectorAll('input[name="return_input_type"]');

        function clearInvalid(el) {
            el?.classList.remove('is-invalid');
        }

        function setDisabled(el, disabled) {
            if (!el) return;
            el.disabled = !!disabled;
        }

        function show(el, showIt) {
            if (!el) return;
            el.classList.toggle('d-none', !showIt);
        }

        function pickedValue(nodeList) {
            const arr = Array.from(nodeList || []);
            const checked = arr.find(r => r.checked);
            return checked ? checked.value : null;
        }

        function resetValue(el) {
            if (!el) return;
            el.value = '';
        }

        function applyOutboundToggle() {
            const mode = pickedValue(outRadios) || 'time';
            const isTime = mode === 'time';

            // görünürlük
            show(outTimeWrap, isTime);
            show(outFlightWrap, !isTime);

            // disabled (request'e gitmesin)
            setDisabled(outTime, !isTime);
            setDisabled(outFlight, isTime);

            // görünmeyeni temizle (snapshot kirlenmesin)
            if (isTime) {
                resetValue(outFlight);
            } else {
                resetValue(outTime);
            }

            clearInvalid(outTime);
            clearInvalid(outFlight);
            pairError?.classList.add('d-none');
        }

        function applyReturnToggle() {
            if (!retRadios || !retRadios.length) return;

            const mode = pickedValue(retRadios) || 'time';
            const isTime = mode === 'time';

            show(retTimeWrap, isTime);
            show(retFlightWrap, !isTime);

            setDisabled(retTime, !isTime);
            setDisabled(retFlight, isTime);

            if (isTime) {
                resetValue(retFlight);
            } else {
                resetValue(retTime);
            }

            clearInvalid(retTime);
            clearInvalid(retFlight);
            pairError?.classList.add('d-none');
        }

        // initial apply
        applyOutboundToggle();
        applyReturnToggle();

        // bind change
        Array.from(outRadios).forEach(r => r.addEventListener('change', applyOutboundToggle));
        Array.from(retRadios).forEach(r => r.addEventListener('change', applyReturnToggle));

        // input değişince invalid temizliği
        [outTime, outFlight, retTime, retFlight].forEach(el => {
            el?.addEventListener('input', () => {
                clearInvalid(outTime);
                clearInvalid(outFlight);
                clearInvalid(retTime);
                clearInvalid(retFlight);
                pairError?.classList.add('d-none');
            });
        });

        // submit validation: seçili moda göre ilgili alan dolu olmalı
        function requiredByRadio(radios, timeEl, flightEl) {
            const mode = pickedValue(radios);
            if (!mode) return true; // güvenli: radio yoksa burada fail ettirmeyelim

            if (mode === 'time') {
                return !!(timeEl && timeEl.value && timeEl.value.trim());
            }

            if (mode === 'flight') {
                return !!(flightEl && flightEl.value && flightEl.value.trim());
            }

            return true;
        }

        function markInvalidByRadio(radios, timeEl, flightEl) {
            const mode = pickedValue(radios) || 'time';
            if (mode === 'time') {
                timeEl?.classList.add('is-invalid');
            } else {
                flightEl?.classList.add('is-invalid');
            }
        }

        bookForm.addEventListener('submit', function (e) {
            let ok = true;

            // Outbound şart
            if (!requiredByRadio(outRadios, outTime, outFlight)) {
                markInvalidByRadio(outRadios, outTime, outFlight);
                ok = false;
            }

            // Return şart (varsa)
            if (retRadios && retRadios.length) {
                if (!requiredByRadio(retRadios, retTime, retFlight)) {
                    markInvalidByRadio(retRadios, retTime, retFlight);
                    ok = false;
                }
            }

            if (!ok) {
                e.preventDefault();
                e.stopPropagation();
                pairError?.classList.remove('d-none');
            }
        });
    }
}
