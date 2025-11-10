import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import 'intl-tel-input/build/css/intlTelInput.css';

export function initPhoneInputs() {
    const inputs = document.querySelectorAll('[data-phone-input]');
    if (!inputs.length) return;

    inputs.forEach((input) => {
        const iti = intlTelInput(input, {
            initialCountry: 'tr',
            nationalMode: true,
            separateDialCode: true,
            autoPlaceholder: 'aggressive',
            placeholderNumberType: 'MOBILE',
        });

        // E.164 değer geldiyse set et
        if (input.value) {
            try {
                iti.setNumber(input.value);
            } catch {}
        }

        const form = input.closest('form');
        if (!form) return;

        form.addEventListener('submit', (event) => {
            const raw = input.value.trim();

            // Boşsa:
            if (!raw) {
                if (input.hasAttribute('required')) {
                    event.preventDefault();
                    showError(input, 'Bu alan zorunludur.');
                } else {
                    clearError(input);
                }
                return;
            }

            if (!iti.isValidNumber()) {
                event.preventDefault();
                showError(input, 'Lütfen geçerli bir telefon numarası girin.');
                return;
            }

            input.value = iti.getNumber(); // E.164
            clearError(input);
        });
    });

    function resolveErrorElement(input) {
        // 1) Tercihen container içindeki .phone-error
        const container = input.closest('[data-phone-container]');
        if (container) {
            let el = container.querySelector('.phone-error');
            if (!el) {
                el = document.createElement('div');
                el.className = 'invalid-feedback d-block phone-error';
                el.style.display = 'none';
                container.appendChild(el);
            }
            return el;
        }

        // 2) Fallback: input'un hemen altına ekle (bayrağı bozmaz)
        let next = input.nextElementSibling;
        if (!next || !next.classList.contains('phone-error')) {
            next = document.createElement('div');
            next.className = 'invalid-feedback d-block phone-error';
            next.style.display = 'none';
            input.insertAdjacentElement('afterend', next);
        }
        return next;
    }

    function showError(input, message) {
        input.classList.add('is-invalid');
        const el = resolveErrorElement(input);
        el.textContent = message;
        el.style.display = 'block';
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        const container = input.closest('[data-phone-container]');
        const els = [];

        if (container) {
            els.push(...container.querySelectorAll('.phone-error'));
        } else {
            const next = input.nextElementSibling;
            if (next && next.classList.contains('phone-error')) {
                els.push(next);
            }
        }

        els.forEach((el) => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }
}
