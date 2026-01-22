// resources/js/ui/date-picker.js
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

import { Turkish } from 'flatpickr/dist/l10n/tr.js';

/**
 * ICR Date Picker (single authority)
 *
 * Rules:
 * - flatpickr is imported ONLY here.
 * - locale is NEVER hardcoded; must be provided by caller (site UI locale).
 * - NO fallback: if locale/contract invalid -> do nothing.
 * - UI display is unified: d.m.Y (and range: d.m.Y - d.m.Y)
 * - Submit format is defined by contract.
 */

const UI_FORMAT = 'd F';
const RANGE_SEP = ' - ';

const LOCALES = Object.freeze({
    tr: Turkish,
    en: null, // flatpickr default (English)
});

const CONTRACTS = Object.freeze({
    // Hotel listing: submit Y-m-d - Y-m-d, UI d.m.Y - d.m.Y
    hotel_listing_range: {
        mode: 'range',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: RANGE_SEP,
        minDate: 'today',
        writeInputValue: true,
    },

    // Hotel details: submit d.m.Y - d.m.Y, UI same
    hotel_details_range_ymd_alt: {
        mode: 'range',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: RANGE_SEP,
        minDate: 'today',
        writeInputValue: true, // flatpickr handles it (dateFormat == UI)
    },

    // Transfer: submit Y-m-d, UI d.m.Y
    transfer_single_ymd_alt: {
        mode: 'single',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: null,
        minDate: 'today',
        writeInputValue: true,
    },

    // Excursion: submit d.m.Y, UI same
    excursion_single_ymd_alt: {
        mode: 'single',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: null,
        minDate: 'today',
        writeInputValue: true,
    },

    // Villa: UI range d.m.Y - d.m.Y; submit is page-specific (hidden fields), so we do not force input value.
    villa_range_ui_dmy_alt: {
        mode: 'range',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: RANGE_SEP,
        minDate: 'today',
        writeInputValue: true,
    },
});

function resolveElement(el) {
    if (!el) return null;
    if (typeof el === 'string') return document.querySelector(el);
    if (el instanceof HTMLElement) return el;
    return null;
}

function normalizeLocaleCode(code) {
    const raw = String(code || '').trim().toLowerCase();
    if (!raw) return '';
    // tr_TR, en_GB, tr-TR, en-US -> tr / en
    return raw.replace('_', '-').split('-')[0];
}

function getLocaleObject(localeCode) {
    const code = normalizeLocaleCode(localeCode);

    // No fallback: if locale is unknown -> return undefined (signal invalid)
    if (!(code in LOCALES)) return undefined;

    return LOCALES[code] || undefined;
}

function buildPayload({ contract, instance, selectedDates }) {
    const fmtSubmit = contract.submitFormat;
    const uiFmt = contract.uiFormat;

    if (contract.mode === 'single') {
        const d = selectedDates && selectedDates[0] ? selectedDates[0] : null;

        return {
            mode: 'single',
            date: d,
            ui: d ? instance.formatDate(d, uiFmt) : '',
            submit: d ? instance.formatDate(d, fmtSubmit) : '',
            ymd: d ? instance.formatDate(d, 'Y-m-d') : '',
        };
    }

    // range
    const start = selectedDates && selectedDates[0] ? selectedDates[0] : null;
    const end = selectedDates && selectedDates[1] ? selectedDates[1] : null;

    const nights =
        start && end
            ? Math.ceil((end.getTime() - start.getTime()) / 86400000)
            : 0;

    return {
        mode: 'range',
        start,
        end,
        nights,
        uiStart: start ? instance.formatDate(start, uiFmt) : '',
        uiEnd: end ? instance.formatDate(end, uiFmt) : '',
        submitStart: start ? instance.formatDate(start, fmtSubmit) : '',
        submitEnd: end ? instance.formatDate(end, fmtSubmit) : '',
        ymdStart: start ? instance.formatDate(start, 'Y-m-d') : '',
        ymdEnd: end ? instance.formatDate(end, 'Y-m-d') : '',
    };
}

/**
 * Fix for Bootstrap input-group + flatpickr altInput:
 * - flatpickr turns original input into type="hidden" and inserts altInput.
 * - If hidden input stays inside .input-group, Bootstrap first/last-child radius logic breaks.
 * - Solution: move hidden input outside input-group; keep altInput inside group before addon.
 * - Accessibility: move original id to altInput so <label for="..."> focuses the visible field.
 */
function fixAltInputInsideBootstrapInputGroup(originalEl, instance) {
    if (!originalEl || !instance) return;

    const hiddenInput = instance.input;   // original, now type="hidden"
    const altInput = instance.altInput;   // visible input
    if (!hiddenInput || !altInput) return;

    const group = originalEl.closest('.input-group');
    if (!group) return;

    // If the group does not contain the hidden input, do nothing (already fixed/relocated)
    if (!group.contains(hiddenInput)) return;

    // Ensure altInput looks/behaves like original input in Bootstrap
    // (flatpickr may not keep all classes)
    const originalClasses = (originalEl.className || '').split(' ').filter(Boolean);
    for (const cls of originalClasses) {
        altInput.classList.add(cls);
    }
    altInput.classList.add('form-control');

    // Keep submit "name" on hidden input only
    altInput.removeAttribute('name');

    // Move id from hidden to altInput so label points to visible element
    const originalId = hiddenInput.getAttribute('id');
    if (originalId && !altInput.getAttribute('id')) {
        altInput.setAttribute('id', originalId);
        hiddenInput.removeAttribute('id');
    }

    // Place altInput before addon (input-group-text) if present
    const addon = group.querySelector('.input-group-text');
    if (addon && addon.parentElement === group) {
        addon.insertAdjacentElement('beforebegin', altInput);
    } else {
        group.appendChild(altInput);
    }

    // Move hidden input out of the input-group to restore Bootstrap border/radius logic
    group.insertAdjacentElement('afterend', hiddenInput);
}

/**
 * @param {{
 *   el: string|HTMLElement,
 *   contract: keyof typeof CONTRACTS,
 *   locale: string,
 *   initialValue?: string|null,
 *   hooks?: {
 *     onValidChange?: (payload: any) => void,
 *     onClear?: () => void,
 *   }
 * }} config
 *
 * @returns {import('flatpickr').Instance|null}
 */
export function initDatePicker(config) {
    const el = resolveElement(config?.el);
    if (!el) return null;

    const contractKey = String(config?.contract || '').trim();
    const contract = CONTRACTS[contractKey];
    if (!contract) return null;

    const localeCode = normalizeLocaleCode(config?.locale);
    if (!localeCode) return null;

    const localeObj = getLocaleObject(localeCode);
    // No fallback: locale unknown -> do nothing
    if (localeObj === undefined && localeCode !== 'en') return null;

    // If initialValue provided, override input's current value before init (deterministic).
    if (typeof config?.initialValue === 'string') {
        el.value = config.initialValue;
    }

    const hooks = config?.hooks || {};

    const fpOptions = {
        mode: contract.mode,
        minDate: contract.minDate ?? null,
        locale: contract.mode === 'range'
            ? { ...(localeObj || {}), rangeSeparator: contract.rangeSeparator || RANGE_SEP }
            : (localeObj || undefined),

        // Submit value format (or UI format when useAltInput=false)
        dateFormat: contract.submitFormat,
        allowInput: true,

        // Range separator if applicable
        ...(contract.mode === 'range' && contract.rangeSeparator
            ? { rangeSeparator: contract.rangeSeparator }
            : {}),

        // UI display unification
        ...(contract.useAltInput
            ? {
                altInput: true,
                altFormat: contract.uiFormat,
            }
            : {}),

        onReady: (selectedDates, _dateStr, instance) => {
            // Bootstrap input-group + altInput fix
            if (contract.useAltInput) {
                fixAltInputInsideBootstrapInputGroup(el, instance);
            }

            // If there is an initial selected date already, make sure hooks can react deterministically
            // (no change to existing behavior; onChange handles later interactions)
            // Intentionally no extra emissions here.
        },

        onChange: (selectedDates, _dateStr, instance) => {
            // Clear
            if (!selectedDates || selectedDates.length === 0) {
                if (typeof hooks.onClear === 'function') {
                    hooks.onClear();
                }
                return;
            }

            // If contract wants deterministic input.value writing (submit contract)
            if (contract.writeInputValue) {
                if (contract.mode === 'single') {
                    const d = selectedDates[0];
                    el.value = d ? instance.formatDate(d, contract.submitFormat) : '';
                } else {
                    const start = selectedDates[0] || null;
                    const end = selectedDates[1] || null;

                    if (!start) {
                        el.value = '';
                    } else if (!end) {
                        el.value = instance.formatDate(start, contract.submitFormat);
                    } else {
                        const a = instance.formatDate(start, contract.submitFormat);
                        const b = instance.formatDate(end, contract.submitFormat);
                        el.value = `${a}${contract.rangeSeparator || RANGE_SEP}${b}`;
                    }
                }
            }

            // Hook payload
            if (typeof hooks.onValidChange === 'function') {
                const payload = buildPayload({
                    contract,
                    instance,
                    selectedDates,
                });

                // For range, only "valid" when both dates selected.
                if (contract.mode === 'range') {
                    if (payload.start && payload.end) {
                        hooks.onValidChange(payload);
                    }
                } else {
                    if (payload.date) {
                        hooks.onValidChange(payload);
                    }
                }
            }
        },
    };

    return flatpickr(el, fpOptions);
}
