import flatpickr from "flatpickr";
import { Turkish } from "flatpickr/dist/l10n/tr.js";

document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('.form-control[type="date"]');

    if (!input) return;

    flatpickr(input, {
        locale: Turkish,
        mode: "range",
        dateFormat: "d.m.Y",
        minDate: "today"
    });
});
