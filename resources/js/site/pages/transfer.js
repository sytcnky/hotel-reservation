import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Turkish } from "flatpickr/dist/l10n/tr.js";

export function initTransferForm() {
    const oneway = document.getElementById('oneway');
    const roundtrip = document.getElementById('roundtrip');
    const returnDateWrapper = document.getElementById('returnDateWrapper');
    const departureInput = document.getElementById('departure_date');
    const returnInput = document.getElementById('return_date');

    if (!oneway || !roundtrip || !returnDateWrapper || !departureInput || !returnInput) return;

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

    const returnPicker = flatpickr(returnInput, {
        locale: Turkish,
        dateFormat: "d.m.Y",
        minDate: null,
    });

    flatpickr(departureInput, {
        locale: Turkish,
        dateFormat: "d.m.Y",
        minDate: "today",
        onChange(selectedDates) {
            if (selectedDates.length) {
                returnPicker.set('minDate', selectedDates[0]);
            } else {
                returnPicker.clear();
                returnPicker.set('minDate', null);
            }
        },
    });
}
