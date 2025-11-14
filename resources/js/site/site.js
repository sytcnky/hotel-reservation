import '../../css/site.scss'
import '@flaticon/flaticon-uicons/css/all/all.css'
import * as bootstrap from 'bootstrap'

import initGallery from './ui/gallery.js';
import { initHotelDetails } from './pages/hotel-details';
import { initTransferForm } from './pages/transfer';
import './pages/home.js';
import './pages/hotel-listing';
import './pages/villa-details.js';
import './components/coupons.js'
import { initExcursionDetails } from './pages/excursion-details';
import { initHelpSearch } from './pages/help';
import { initPayment } from './pages/payment';
import { initPhoneInputs } from './ui/phone-input';
import { initGuestPicker } from './ui/guestpicker.js';

document.addEventListener('DOMContentLoaded', () => {
    initGuestPicker(document);

    // === Galeri ===
    initGallery();

    // === Sayfa modülleri (DOM hazır olunca çağrılmalı) ===
    initExcursionDetails();
    initTransferForm();
    initHotelDetails();
    initPayment();
    initHelpSearch();
    initPhoneInputs();

    // === Dil seçimi butonları ===
    document.querySelectorAll('.dropdown-menu .btn-group').forEach(group => {
        const buttons = group.querySelectorAll('.btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    });

    // === Dropdown içeriğinde tıklama menüyü kapatmasın ===
    const dropdown = document.getElementById('langCurrencyDropdown');
    const menu = dropdown?.closest('.dropdown')?.querySelector('.dropdown-menu');
    if (menu) {
        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // === Bayrak tıklama ===
    document.querySelectorAll('[data-flag]').forEach(btn => {
        btn.addEventListener('click', () => {
            const flagSrc = btn.getAttribute('data-flag');
            const flagAlt = btn.getAttribute('data-lang');
            const selectedImg = document.getElementById('selectedFlag');
            if (selectedImg) {
                selectedImg.src = flagSrc;
                selectedImg.alt = flagAlt.toUpperCase();
            }

            btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // === Tooltipler ===
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    tooltipTriggerList.forEach(el => {
        new bootstrap.Tooltip(el)
    });

    // === Misafir artır/azalt (GLOBAL) ===
    function increase(type) {
        const input = document.getElementById(type + 'Count');
        let val = parseInt(input.value);
        if (val < 10) {
            input.value = val + 1;
            document.getElementById('input' + capitalize(type)).value = val + 1;
            updateGuestSummary();
        }
    }

    function decrease(type) {
        const input = document.getElementById(type + 'Count');
        let val = parseInt(input.value);
        if (type === 'adult' && val > 1) {
            input.value = val - 1;
            document.getElementById('input' + capitalize(type)).value = val - 1;
            updateGuestSummary();
        }
        if (type === 'child' && val > 0) {
            input.value = val - 1;
            document.getElementById('input' + capitalize(type)).value = val - 1;
            updateGuestSummary();
        }
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function updateGuestSummary() {
        const adult = parseInt(document.getElementById('adultCount')?.value || 0);
        const child = parseInt(document.getElementById('childCount')?.value || 0);

        let summary = `${adult} Yetişkin`;
        if (child > 0) {
            summary += `, ${child} Çocuk`;
        }

        const guestSummaryEl = document.getElementById('guestSummary');
        if (guestSummaryEl) guestSummaryEl.textContent = summary;
    }

    // === Global erişim ===
    window.increase = increase;
    window.decrease = decrease;
});
