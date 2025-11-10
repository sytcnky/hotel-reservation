import { initGuestPicker } from '../ui/guestpicker.js';

export function initHotelDetails() {
    initRoomToggles();
    initGuestPicker(document);
}

function initRoomToggles() {
    const roomCards = document.querySelectorAll('.room-card');
    if (!roomCards.length) return;

    roomCards.forEach((card) => {
        const toggleBtn = card.querySelector('.room-toggle-details');
        const wrapper = card.querySelector('.room-details-wrapper');
        const content = card.querySelector('.room-details');
        const gallery = card.querySelector('.gallery');

        if (!toggleBtn || !wrapper || !content) return;

        toggleBtn.addEventListener('click', () => {
            const isOpen = card.classList.toggle('expanded');
            toggleBtn.classList.toggle('bg-warning', isOpen);
            toggleBtn.classList.toggle('bg-white', !isOpen);

            if (isOpen) {
                if (gallery && !gallery.dataset.initialized) {
                    import('../ui/gallery').then(({ default: initGallery }) => {
                        initGallery(gallery);
                    });
                }

                wrapper.style.height = content.scrollHeight + 'px';

                wrapper.addEventListener('transitionend', () => {
                    if (card.classList.contains('expanded')) {
                        wrapper.style.height = 'auto';
                    }
                }, { once: true });

            } else {
                wrapper.style.height = content.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    wrapper.style.height = '0px';
                });
            }
        });
    });
}
