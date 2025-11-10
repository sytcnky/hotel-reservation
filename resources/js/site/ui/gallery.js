export default function initGallery(gallerySelector = '.gallery') {
    const galleries = typeof gallerySelector === 'string'
        ? document.querySelectorAll(gallerySelector)
        : [gallerySelector];

    galleries.forEach((gallery) => {
        if (!gallery || gallery.dataset.initialized) return;
        gallery.dataset.initialized = 'true';

        let currentIndex = 0;
        const images = gallery.querySelectorAll('.gallery-image');
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        const thumbContainer = gallery.querySelector('.thumbnail-scroll') || (thumbs.length > 0 ? thumbs[0].parentElement : null);

        function scrollThumbIntoView(el) {
            const container = el.parentElement;
            const containerRect = container.getBoundingClientRect();
            const elRect = el.getBoundingClientRect();

            if (elRect.left < containerRect.left) {
                container.scrollBy({ left: elRect.left - containerRect.left - 10, behavior: 'smooth' });
            } else if (elRect.right > containerRect.right) {
                container.scrollBy({ left: elRect.right - containerRect.right + 10, behavior: 'smooth' });
            }
        }

        function showImage(index) {
            if (index < 0 || index >= images.length) return;

            images[currentIndex].classList.add('d-none');
            images[index].classList.remove('d-none');
            currentIndex = index;

            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('active-thumb', i === index);
            });

            scrollThumbIntoView(thumbs[index]);
        }

        function changeImage(direction) {
            let nextIndex = currentIndex + direction;
            if (nextIndex < 0) nextIndex = images.length - 1;
            if (nextIndex >= images.length) nextIndex = 0;
            showImage(nextIndex);
        }

        // Drag scroll (mouse + touch)
        let isDragging = false;
        let dragStartX = 0;
        let scrollStartX = 0;

        if (thumbContainer) {
            thumbContainer.addEventListener('mousedown', (e) => {
                isDragging = false;
                dragStartX = e.pageX;
                scrollStartX = thumbContainer.scrollLeft;

                const onMouseMove = (e) => {
                    const dx = e.pageX - dragStartX;
                    if (Math.abs(dx) > 5) isDragging = true;
                    thumbContainer.scrollLeft = scrollStartX - dx;
                };

                const onMouseUp = () => {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    setTimeout(() => { isDragging = false; }, 50);
                };

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            thumbContainer.addEventListener('touchstart', (e) => {
                dragStartX = e.touches[0].pageX;
                scrollStartX = thumbContainer.scrollLeft;
            }, { passive: true });

            thumbContainer.addEventListener('touchmove', (e) => {
                const dx = e.touches[0].pageX - dragStartX;
                if (Math.abs(dx) > 5) e.preventDefault();
                thumbContainer.scrollLeft = scrollStartX - dx;
            }, { passive: false });
        }

        thumbs.forEach((thumb, i) => {
            thumb.addEventListener('click', (e) => {
                if (isDragging) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return;
                }
                e.preventDefault();
                showImage(i);
            });
        });

        let swipeStartX = 0;
        gallery.addEventListener('touchstart', e => swipeStartX = e.changedTouches[0].screenX);

        gallery.addEventListener('touchend', e => {
            const diff = e.changedTouches[0].screenX - swipeStartX;
            if (diff > 50) changeImage(-1);
            else if (diff < -50) changeImage(1);
        });

        const mainGallery = gallery.querySelector('.main-gallery');
        if (mainGallery) {
            mainGallery.addEventListener('click', () => changeImage(1));
        }

        if (images.length === 0) return;
        showImage(0);
    });
}
