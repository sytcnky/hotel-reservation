// resources/js/pages/payment.js
export function initPayment() {
    const form       = document.getElementById('paymentForm');
    const submitBtn  = document.getElementById('btnSubmitPayment');

    const holderInput   = document.getElementById('ccHolder');
    const numberInput   = document.getElementById('ccNumber');
    const expMonthInput = document.getElementById('ccExpiryMonth');
    const expYearInput  = document.getElementById('ccExpiryYear');
    const cvvInput      = document.getElementById('ccCvv');
    const termsInput    = document.getElementById('terms');

    // Kart önizleme elemanları
    const inner         = document.getElementById('ccInner');
    const holderVis     = document.getElementById('ccHolderVis');
    const numberVis     = document.getElementById('ccNumberVis');
    const expVis        = document.getElementById('ccExpiryVis');
    const cvvVis        = document.getElementById('ccCvvVis');
    const brandLogo     = document.getElementById('ccBrandLogo');
    const brandLogoBack = document.getElementById('ccBrandLogoBack');

    // Sayfa değilse ya da kritik alanlar yoksa çık
    if (
        !form ||
        !submitBtn ||
        !holderInput ||
        !numberInput ||
        !expMonthInput ||
        !expYearInput ||
        !cvvInput ||
        !termsInput
    ) {
        return;
    }

    // --- VALIDASYON STATE ---
    let holderTouched = false;
    let numberTouched = false;
    let expiryTouched = false; // ay veya yıl blur olunca
    let cvvTouched    = false;
    let termsTouched  = false;

    // --- HELPERS (VALIDASYON) ---
    function onlyDigits(value) {
        return (value || '').replace(/\D+/g, '');
    }

    // Basit Luhn (13–19 hane)
    function isValidCardNumber(num) {
        const digits = onlyDigits(num);
        if (digits.length < 13 || digits.length > 19) {
            return false;
        }

        let sum = 0;
        let shouldDouble = false;

        for (let i = digits.length - 1; i >= 0; i--) {
            let d = parseInt(digits.charAt(i), 10);

            if (shouldDouble) {
                d = d * 2;
                if (d > 9) d -= 9;
            }

            sum += d;
            shouldDouble = !shouldDouble;
        }

        return (sum % 10) === 0;
    }

    // Ay/Yıl ayrı alanlardan validasyon
    function isValidExpiryParts(mmRaw, yyRaw) {
        const mmStr = (mmRaw || '').trim();
        const yyStr = (yyRaw || '').trim();

        if (!mmStr || !yyStr) {
            return false;
        }

        const mm = parseInt(mmStr, 10);
        const yy = parseInt(yyStr, 10);

        if (Number.isNaN(mm) || Number.isNaN(yy)) return false;
        if (mm < 1 || mm > 12) return false;

        const fullYear = 2000 + yy;

        const now       = new Date();
        const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const expMonth  = new Date(fullYear, mm - 1, 1);

        return expMonth >= thisMonth;
    }

    function isValidCvv(value) {
        return /^\d{3,4}$/.test(value || '');
    }

    function setFieldValidity(inputEl, isValid, touched) {
        if (!inputEl) return;

        const value = (inputEl.value || '').trim();
        const feedback = inputEl.nextElementSibling;

        // Kritik: feedback yoksa hiçbir işlem yapma (asla tahmin yok)
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            return;
        }

        // Dokunulmamış / boş → state gösterme
        if (!touched || !value) {
            inputEl.classList.remove('is-invalid');
            feedback.classList.remove('d-block');
            return;
        }

        if (isValid) {
            inputEl.classList.remove('is-invalid');
            feedback.classList.remove('d-block');
        } else {
            inputEl.classList.add('is-invalid');
            feedback.classList.add('d-block');
        }
    }

    function validateForm() {
        const holderVal = holderInput.value.trim();
        const holderOk  = holderVal.length >= 3;

        const numberVal = numberInput.value;
        const numberOk  = isValidCardNumber(numberVal);

        const mmVal = expMonthInput.value;
        const yyVal = expYearInput.value;
        const expiryOk = isValidExpiryParts(mmVal, yyVal);

        const cvvVal = cvvInput.value;
        const cvvOk  = isValidCvv(cvvVal);

        const termsOk = termsInput.checked;

        // Bootstrap invalid state (yalnız touched ise göster)
        setFieldValidity(holderInput,   holderOk,  holderTouched);
        setFieldValidity(numberInput,   numberOk,  numberTouched);
        setFieldValidity(expMonthInput, expiryOk,  expiryTouched);
        setFieldValidity(expYearInput,  expiryOk,  expiryTouched);
        setFieldValidity(cvvInput,      cvvOk,     cvvTouched);

        if (termsTouched && !termsOk) {
            termsInput.classList.add('is-invalid');
        } else {
            termsInput.classList.remove('is-invalid');
        }

        const allValid = holderOk && numberOk && expiryOk && cvvOk && termsOk;
        submitBtn.disabled = !allValid;
    }

    // Desteklenen kart markaları (İş Bankası sanal POS’a göre)
    const BRAND_LOGOS = {
        visa:       '/images/brands/visa.svg',
        mastercard: '/images/brands/mastercard.svg',
        amex:       '/images/brands/american-express.svg',
        unionpay:   '/images/brands/unionpay.svg',
        jcb:        '/images/brands/jcb.svg',
        troy:       '/images/brands/troy.svg',
    };

    // Troy (9792), Visa (4), Mastercard (51-55, 2221-2720), Amex (34/37)
    // Troy (9792), Visa (4), MasterCard (51–55, 2221–2720), Amex (34/37),
// UnionPay (62...), JCB (3528–3589)
    function detectBrand(digits) {
        if (!digits || digits.length < 1) {
            return 'unknown';
        }

        // Visa
        if (/^4\d*/.test(digits)) {
            return 'visa';
        }

        // MasterCard
        if (/^(5[1-5]\d*|2(2[2-9]\d*|[3-6]\d*|7[01]\d*|720\d*))/.test(digits)) {
            return 'mastercard';
        }

        // American Express
        if (/^(34|37)\d*/.test(digits)) {
            return 'amex';
        }

        // UnionPay
        if (/^62\d*/.test(digits)) {
            return 'unionpay';
        }

        // JCB (3528–3589)
        if (/^35(2[89]|[3-8][0-9])\d*/.test(digits)) {
            return 'jcb';
        }

        // Troy
        if (/^9792\d*/.test(digits)) {
            return 'troy';
        }

        // Tanınmayan başka bir marka (Discover, Diners vs.) → logo gösterme
        return 'unknown';
    }


    function formatNumber(digits, brand) {
        if (brand === 'amex') {
            const g1 = digits.substring(0, 4);
            const g2 = digits.substring(4, 10);
            const g3 = digits.substring(10, 15);
            return [g1, g2, g3].filter(Boolean).join(' ');
        }
        return digits.match(/.{1,4}/g)?.join(' ') ?? digits;
    }

    function maxLenFor(brand) {
        return brand === 'amex' ? 15 : 16;
    }

    function setBrand(brand) {
        const src = BRAND_LOGOS[brand] || null;

        if (brandLogo) {
            if (src) {
                brandLogo.src = src;
                brandLogo.style.display = 'block';
            } else {
                brandLogo.removeAttribute('src');
                brandLogo.style.display = 'none';
            }
        }

        if (brandLogoBack) {
            if (src) {
                brandLogoBack.src = src;
                brandLogoBack.style.display = 'block';
            } else {
                brandLogoBack.removeAttribute('src');
                brandLogoBack.style.display = 'none';
            }
        }

        // CVV maxlength / placeholder'a dokunmuyoruz (HTML ne ise o).
    }

    function updateExpiryVis() {
        if (!expVis) return;

        let mm = (expMonthInput.value || '').replace(/\D/g, '').slice(0, 2);
        let yy = (expYearInput.value  || '').replace(/\D/g, '').slice(0, 2);

        if (mm !== expMonthInput.value) {
            expMonthInput.value = mm;
        }
        if (yy !== expYearInput.value) {
            expYearInput.value = yy;
        }

        if (!mm && !yy) {
            expVis.textContent = 'MM/YY';
        } else {
            const mmP = mm.padStart(2, '0');
            const yyP = yy.padStart(2, '0');
            expVis.textContent = mmP + '/' + yyP;
        }
    }

    // --- EVENTLER ---

    // Kart üzerindeki isim → önizleme + validasyon
    holderInput.addEventListener('input', function () {
        const val = holderInput.value.trim();
        if (holderVis) {
            holderVis.textContent = val ? val.toUpperCase() : 'AD SOYAD';
        }
        validateForm();
    });
    holderInput.addEventListener('blur', function () {
        holderTouched = true;
        validateForm();
    });

    // Kart numarası → marka + format + önizleme + validasyon
    numberInput.addEventListener('input', function () {
        let digits = (numberInput.value || '').replace(/\D/g, '');
        const brand = detectBrand(digits);
        setBrand(brand);

        digits = digits.slice(0, maxLenFor(brand));
        const formatted = formatNumber(digits, brand);

        numberInput.value = formatted;
        if (numberVis) {
            numberVis.textContent = formatted || '•••• •••• •••• ••••';
        }

        validateForm();
    });
    numberInput.addEventListener('blur', function () {
        numberTouched = true;
        validateForm();
    });

    // SKT ay / yıl → önizleme + validasyon
    expMonthInput.addEventListener('input', function () {
        updateExpiryVis();
        validateForm();
    });
    expYearInput.addEventListener('input', function () {
        updateExpiryVis();
        validateForm();
    });
    expMonthInput.addEventListener('blur', function () {
        expiryTouched = true;
        validateForm();
    });
    expYearInput.addEventListener('blur', function () {
        expiryTouched = true;
        validateForm();
    });

    // CVV → kart flip + mask + validasyon
    if (inner) {
        cvvInput.addEventListener('focus', function () {
            inner.style.transform = 'rotateY(180deg)';
        });
        cvvInput.addEventListener('blur', function () {
            inner.style.transform = '';
            cvvTouched = true;
            validateForm();
        });
    } else {
        cvvInput.addEventListener('blur', function () {
            cvvTouched = true;
            validateForm();
        });
    }

    cvvInput.addEventListener('input', function () {
        const clean = (cvvInput.value || '').replace(/\D/g, '');
        const max   = parseInt(cvvInput.getAttribute('maxlength') || '4', 10);

        cvvInput.value = clean.slice(0, max);

        if (cvvVis) {
            cvvVis.textContent = cvvInput.value
                ? '•'.repeat(cvvInput.value.length)
                : '•••';
        }

        validateForm();
    });

    // Sözleşme → validasyon
    termsInput.addEventListener('change', function () {
        termsTouched = true;
        validateForm();
    });

    // İleride submit aşamasında da kullanırsın (ör. AJAX post öncesi):
    // form.addEventListener('submit', function (e) {
    //     termsTouched = true;
    //     holderTouched = numberTouched = expiryTouched = cvvTouched = true;
    //     validateForm();
    //     if (submitBtn.disabled) {
    //         e.preventDefault();
    //     }
    // });

    // --- SUBMIT: double-submit guard + loading state ---
    const labelEl   = submitBtn.querySelector('.btn-label');
    const loadingEl = submitBtn.querySelector('.btn-loading');

    form.addEventListener('submit', function (e) {
        // Önce: form valid mi? (touched'ları açıp bir kez daha kontrol et)
        termsTouched  = true;
        holderTouched = true;
        numberTouched = true;
        expiryTouched = true;
        cvvTouched    = true;

        validateForm();

        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        // Double submit engeli
        if (form.dataset.submitted === '1') {
            e.preventDefault();
            return;
        }

        form.dataset.submitted = '1';
        submitBtn.disabled = true;

        if (labelEl) {
            labelEl.classList.add('d-none');
        }
        if (loadingEl) {
            loadingEl.classList.remove('d-none');
        }
    });

    // İlk state
    updateExpiryVis();
    validateForm();
}
