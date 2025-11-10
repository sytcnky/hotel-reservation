// resources/js/pages/payment.js
export function initPayment() {
    const inner   = document.getElementById('ccInner');
    const holder  = document.getElementById('ccHolder');
    const holderV = document.getElementById('ccHolderVis');
    const number  = document.getElementById('ccNumber');
    const numberV = document.getElementById('ccNumberVis');
    const expiry  = document.getElementById('ccExpiry');
    const expiryV = document.getElementById('ccExpiryVis');
    const cvv     = document.getElementById('ccCvv');
    const cvvV    = document.getElementById('ccCvvVis');
    const brandLogo     = document.getElementById('ccBrandLogo');
    const brandLogoBack = document.getElementById('ccBrandLogoBack');
    const luhnState = document.getElementById('ccLuhnState');

    // Sayfa değilse çık
    if (!inner || !holder || !number || !expiry || !cvv) return;

    const BRAND_LOGOS = {
        visa:       '/images/brands/visa.svg',
        mastercard: '/images/brands/mastercard.svg',
        amex:       '/images/brands/amex.svg',
        troy:       '/images/brands/troy.svg',
    };

    // Troy (9792), Visa (4), Mastercard (51-55, 2221-2720), Amex (34/37)
    const detectBrand = (digits) => {
        if (/^4\d*/.test(digits)) return 'visa';
        if (/^(5[1-5]\d*|2(2[2-9]\d*|[3-6]\d*|7[01]\d*|720\d*))/.test(digits)) return 'mastercard';
        if (/^(34|37)\d*/.test(digits)) return 'amex';
        if (/^9792\d*/.test(digits)) return 'troy';
        return 'unknown';
    };

    const formatNumber = (digits, brand) => {
        if (brand === 'amex') {
            const g1 = digits.substring(0,4);
            const g2 = digits.substring(4,10);
            const g3 = digits.substring(10,15);
            return [g1,g2,g3].filter(Boolean).join(' ');
        }
        return digits.match(/.{1,4}/g)?.join(' ') ?? digits;
    };
    const maxLenFor = (brand) => brand === 'amex' ? 15 : 16;
    const cvvLenFor = (brand) => brand === 'amex' ? 4 : 3;

    const luhn = (num) => {
        if (!/^\d{13,19}$/.test(num)) return false;
        let sum = 0, dbl = false;
        for (let i = num.length - 1; i >= 0; i--) {
            let d = parseInt(num[i],10);
            if (dbl) { d *= 2; if (d > 9) d -= 9; }
            sum += d; dbl = !dbl;
        }
        return (sum % 10) === 0;
    };

    const setBrand = (brand) => {
        const known = BRAND_LOGOS[brand];
        if (brandLogo) {
            if (known) { brandLogo.src = known; brandLogo.style.display = 'block'; }
            else { brandLogo.removeAttribute('src'); brandLogo.style.display = 'none'; }
        }
        if (brandLogoBack) {
            if (known) { brandLogoBack.src = known; brandLogoBack.style.display = 'block'; }
            else { brandLogoBack.removeAttribute('src'); brandLogoBack.style.display = 'none'; }
        }
        // CVV uzunluğu / placeholder
        const cvvMax = cvvLenFor(brand);
        cvv.setAttribute('maxlength', cvvMax);
        cvv.setAttribute('placeholder', '•'.repeat(cvvMax));
    };

    // Holder
    holder.addEventListener('input', () => {
        holderV.textContent = holder.value.trim() ? holder.value.toUpperCase() : 'AD SOYAD';
    });

    // Number
    number.addEventListener('input', () => {
        let digits = number.value.replace(/\D/g, '');
        let brand = detectBrand(digits);
        setBrand(brand);

        digits = digits.slice(0, maxLenFor(brand));
        const formatted = formatNumber(digits, brand);
        number.value = formatted;
        numberV.textContent = formatted || '•••• •••• •••• ••••';

        const pass = (digits.length === maxLenFor(brand)) && luhn(digits);
        luhnState.innerHTML = pass
            ? '<span class="text-success"><i class="fi fi-rr-check-circle"></i> Geçerli</span>'
            : '<span class="text-muted"><i class="fi fi-rr-credit-card"></i></span>';
    });

    // Expiry MM/YY
    expiry.addEventListener('input', () => {
        let v = expiry.value.replace(/\D/g,'').slice(0,4);
        if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
        expiry.value = v;
        expiryV.textContent = v || 'MM/YY';
    });

    // CVV flip + mask
    cvv.addEventListener('focus', () => { inner.style.transform = 'rotateY(180deg)'; });
    cvv.addEventListener('blur',  () => { inner.style.transform = ''; });
    cvv.addEventListener('input', () => {
        const clean = cvv.value.replace(/\D/g,'');
        cvv.value = clean.slice(0, parseInt(cvv.getAttribute('maxlength')||'3',10));
        cvvV.textContent = '•'.repeat(cvv.value.length) || '•••';
    });
}
