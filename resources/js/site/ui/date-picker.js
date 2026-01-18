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
    excursion_single_dmy: {
        mode: 'single',
        submitFormat: 'Y-m-d',
        uiFormat: UI_FORMAT,
        useAltInput: true,
        rangeSeparator: null,
        minDate: 'today',
        writeInputValue: true,
    },

    // Villa: UI range d.m.Y - d.m.Y; submit is page-specific (hidden fields), so we do not force input value.
    excursion_single_ymd_alt: {
        mode: 'range',
        submitFormat: UI_FORMAT,
        uiFormat: UI_FORMAT,
        useAltInput: false,
        rangeSeparator: RANGE_SEP,
        minDate: 'today',
        writeInputValue: false,
    },

});

function resolveElement(el) {
    if (!el) return null;
    if (typeof el === 'string') return document.querySelector(el);
    if (el instanceof HTMLElement) return el;
    return null;
}

function normalizeLocaleCode(code) {
    return String(code || '').trim().toLowerCase();
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
        locale: localeObj, // en -> undefined (default). tr -> Turkish object.

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
