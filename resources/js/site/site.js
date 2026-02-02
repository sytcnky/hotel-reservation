// resources/js/site.js

import '@flaticon/flaticon-uicons/css/all/all.css';
import * as bootstrap from 'bootstrap';

import initGallery from './ui/gallery.js';

import { initHome } from './pages/home';
import { initHotelListing } from './pages/hotel-listing';
import { initHotelDetails } from './pages/hotel-details';
import { initTransferForm } from './pages/transfer';
import { initExcursionDetails } from './pages/excursion-details';
import { initVillaDetails } from './pages/villa-details.js';
import { initHelpSearch } from './pages/help';
import { initPayment } from './pages/payment';
import { initPhoneInputs } from './ui/phone-input';
import { initGuestPicker } from './ui/guestpicker.js';
import { initAccountTickets } from './pages/account-tickets';
import './components/coupons.js';

document.addEventListener('DOMContentLoaded', () => {
    // === Ortak modüller (her sayfada) ===
    initGuestPicker(document);
    initGallery();
    initPhoneInputs();

    // === Dil seçimi butonları ===
    document.querySelectorAll('.dropdown-menu .btn-group[data-lang-toggle]').forEach(group => {
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
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // === Currency change confirm modal (cart doluyken) ===
    const currencyModalEl = document.getElementById('currencyChangeModal');
    if (currencyModalEl) {
        const currencyButtons = document.querySelectorAll('.js-currency-switch');
        const confirmBtn = document.getElementById('confirmCurrencyChange');

        if (currencyButtons.length > 0 && confirmBtn) {
            const modal = new bootstrap.Modal(currencyModalEl);

            let pendingAction = null;

            currencyButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();

                    const action = btn.dataset.currencyAction;
                    if (!action) return;

                    pendingAction = action;

                    modal.show();
                });
            });

            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();

                if (!pendingAction) return;

                const form = document.getElementById('currencySwitchForm');
                if (!form) return;

                const confirmField = document.getElementById('currencyConfirmField');
                if (!confirmField) return;

                confirmField.value = '1';
                form.setAttribute('action', pendingAction);

                form.submit();
            });
        }
    }

    // === Sayfa bazlı init dispatch (tek otorite: body[data-page]) ===
    const page = document.body?.dataset?.page || '';

    switch (page) {
        case 'home':
            initHome();
            break;

        case 'hotel-listing':
            initHotelListing();
            break;

        case 'hotel-details':
            initHotelDetails();
            break;

        case 'villa-details':
            initVillaDetails();
            break;

        case 'transfer':
            initTransferForm();
            break;

        case 'excursion-details':
            initExcursionDetails();
            break;

        case 'help':
            initHelpSearch();
            break;

        case 'payment':
            initPayment();
            break;

        case 'account-tickets':
            initAccountTickets(document);
            break;

        default:
            // no-op
            break;
    }
});
