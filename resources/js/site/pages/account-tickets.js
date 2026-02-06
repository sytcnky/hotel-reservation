// resources/js/site/pages/account-tickets.js

/* -------------------------------------------------------------------------- */
/*  tickets page                                                              */
/* -------------------------------------------------------------------------- */
function initTicketsIndexPage(root = document) {
    const list = root.getElementById('ticketList');
    const sortSelect = root.getElementById('sortSelect');
    const statusFilter = root.getElementById('statusFilter');

    if (!list || !sortSelect || !statusFilter) {
        return;
    }

    function getCards() {
        return Array.from(list.querySelectorAll('.ticket-card'));
    }

    function parseWhen(card) {
        const t = Date.parse(card.getAttribute('data-when') || '');
        return Number.isNaN(t) ? 0 : t;
    }

    function applyFilter(cards) {
        const wanted = String(statusFilter.value || 'all').toLowerCase();
        cards.forEach((c) => {
            const st = String(c.getAttribute('data-status') || '').toLowerCase();
            c.classList.toggle('d-none', !(wanted === 'all' || st === wanted));
        });
    }

    function applySort(cards) {
        const mode = sortSelect.value; // date_desc | date_asc
        const visible = cards.filter((c) => !c.classList.contains('d-none'));

        visible.sort((a, b) => {
            return mode === 'date_asc'
                ? parseWhen(a) - parseWhen(b)
                : parseWhen(b) - parseWhen(a);
        });

        const frag = document.createDocumentFragment();
        visible.forEach((c) => frag.appendChild(c));

        // sadece görünürleri başa taşıyoruz (mevcut davranışın aynısı)
        list.prepend(frag);
    }

    function refresh() {
        const cards = getCards();
        applyFilter(cards);
        applySort(cards);
    }

    sortSelect.addEventListener('change', refresh);
    statusFilter.addEventListener('change', refresh);

    refresh();
}

export function initAccountTickets(root = document) {
    // Bu modül yalnızca ilgili sayfalarda çalışsın (guard)
    const replyForm = root.getElementById('replyForm');          // ticket-detail
    const createForm = root.getElementById('ticketCreateForm');  // ticket-create

    if (!replyForm && !createForm) {
        return;
    }

    if (replyForm) {
        initTicketReplyPage(replyForm);
    }

    if (createForm) {
        initTicketCreatePage(createForm);
    }

    initTicketsIndexPage(root);
}

/* -------------------------------------------------------------------------- */
/*  Shared helpers                                                            */
/* -------------------------------------------------------------------------- */

function initBootstrapValidation(form) {
    // Bootstrap validation standardı
    // (Tarayıcı native balonları yerine .was-validated + invalid-feedback)
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);
}

function initAttachmentsController(options) {
    const {
        form,
        listEl,
        addBtnEl,
        addBtnTextEl,
        hintEl = null,
        submitBtnEl = null,
        errorBoxEl = null,
        // detail sayfasında yalnızca body zorunlu, create sayfasında body+subject+category(+order) vb. zorunlu.
        isFormValidFn = null,
    } = options;

    if (!form || !listEl || !addBtnEl || !addBtnTextEl) {
        return;
    }

    // i18n mesajları: hardcoded fallback YOK (kontrat)
    const textAdd = (addBtnEl.getAttribute('data-text-add') || '').trim();
    const textAddMore = (addBtnEl.getAttribute('data-text-add-more') || '').trim();

    const msgFileTooLarge = (addBtnEl.getAttribute('data-msg-file-too-large') || '').trim();
    const msgFileTypeUnsupported = (addBtnEl.getAttribute('data-msg-file-type-unsupported') || '').trim();
    const msgFileInvalidGeneric = (addBtnEl.getAttribute('data-msg-file-invalid-generic') || '').trim();

    // Bu modül submit enable/disable akışında otorite olduğu için, metinler yoksa init etme.
    // (Aksi halde hardcoded’a düşmek gerekir, bu yasak.)
    if (
        !textAdd ||
        !textAddMore ||
        !msgFileTooLarge ||
        !msgFileTypeUnsupported ||
        !msgFileInvalidGeneric
    ) {
        return;
    }

    // Güvenlik: yalnızca resim uzantıları + 2MB
    const allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    const allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    const maxBytes = 2 * 1024 * 1024;

    function setSubmitEnabled(enabled) {
        if (!submitBtnEl) return;
        submitBtnEl.disabled = !enabled;
    }

    function showClientError(msg) {
        if (!errorBoxEl) return;
        errorBoxEl.textContent = msg;
        errorBoxEl.classList.remove('d-none');
        errorBoxEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearClientError() {
        if (!errorBoxEl) return;
        errorBoxEl.textContent = '';
        errorBoxEl.classList.add('d-none');
    }

    function updateButtonText() {
        addBtnTextEl.textContent = listEl.children.length > 0 ? textAddMore : textAdd;
    }

    function updateHintVisibility() {
        if (!hintEl) return;
        hintEl.classList.toggle('d-none', listEl.children.length === 0);
    }

    function validateFile(file) {
        if (!file) return null;

        const name = (file.name || '').toLowerCase();
        const ext = name.includes('.') ? name.split('.').pop() : '';
        const mime = (file.type || '').toLowerCase();

        if (file.size > maxBytes) {
            return msgFileTooLarge;
        }

        const extOk = allowedExt.includes(ext);
        const mimeOk = allowedMime.includes(mime);

        if (!extOk && !mimeOk) {
            return msgFileTypeUnsupported;
        }

        return null;
    }

    function hasAnyInvalidSelection() {
        const inputs = listEl.querySelectorAll('input[type="file"][name="attachments[]"]');
        for (const input of inputs) {
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) continue;

            const err = validateFile(file);
            if (err) return true;
        }
        return false;
    }

    function refreshSubmitState() {
        if (!submitBtnEl) return;

        const requiredOk = typeof isFormValidFn === 'function' ? isFormValidFn() : true;
        const filesOk = !hasAnyInvalidSelection();
        const hasVisibleError = (errorBoxEl && !errorBoxEl.classList.contains('d-none'));

        setSubmitEnabled(requiredOk && filesOk && !hasVisibleError);
    }

    function makeRow() {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-stretch';

        const inputWrap = document.createElement('div');
        inputWrap.className = 'flex-grow-1';

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'attachments[]';
        input.className = 'form-control form-control-sm h-100';
        input.accept = '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp';

        inputWrap.appendChild(input);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger btn-sm d-inline-flex justify-content-center';
        removeBtn.style.width = '44px';
        removeBtn.innerHTML = '<i class="fi-br-cross-small align-middle"></i>';

        removeBtn.addEventListener('click', function () {
            row.remove();
            updateButtonText();
            updateHintVisibility();
            refreshSubmitState();
        });

        row.appendChild(inputWrap);
        row.appendChild(removeBtn);

        return row;
    }

    // Add attachment row
    addBtnEl.addEventListener('click', function () {
        listEl.appendChild(makeRow());
        updateButtonText();
        updateHintVisibility();
        refreshSubmitState();
    });

    // File change validation (scoped delegation)
    form.addEventListener('change', function (e) {
        const el = e.target;
        if (!el || el.type !== 'file' || el.name !== 'attachments[]') return;

        clearClientError();

        const file = el.files && el.files[0] ? el.files[0] : null;
        const err = validateFile(file);

        if (err) {
            el.value = '';
            showClientError(err);
        }

        refreshSubmitState();
    });

    // Form inputs change -> submit state
    form.addEventListener('input', refreshSubmitState);
    form.addEventListener('change', refreshSubmitState);

    // Submit anında son kontrol (server’a invalid gitmesin)
    form.addEventListener('submit', function (event) {
        clearClientError();

        if (hasAnyInvalidSelection()) {
            event.preventDefault();
            event.stopPropagation();
            showClientError(msgFileInvalidGeneric);
            refreshSubmitState();
        }
    }, false);

    // init UI
    updateButtonText();
    updateHintVisibility();

    // default disabled (isteğin: default disable)
    if (submitBtnEl) {
        submitBtnEl.disabled = true;
    }

    refreshSubmitState();

    return { refreshSubmitState };
}

/* -------------------------------------------------------------------------- */
/*  ticket-detail page                                                        */
/* -------------------------------------------------------------------------- */

function initTicketReplyPage(form) {
    initBootstrapValidation(form);

    const bodyEl = document.getElementById('replyMessage');
    const submitBtn = document.getElementById('replySubmitBtn');

    const isFormValidFn = () => {
        if (!bodyEl) return false;
        return String(bodyEl.value || '').trim().length > 0;
    };

    initAttachmentsController({
        form,
        listEl: document.getElementById('attachmentsList'),
        addBtnEl: document.getElementById('addAttachmentBtn'),
        addBtnTextEl: document.getElementById('addAttachmentBtnText'),
        hintEl: document.getElementById('attachmentsHint'),
        submitBtnEl: submitBtn,
        errorBoxEl: document.getElementById('attachmentsError'),
        isFormValidFn,
    });
}

/* -------------------------------------------------------------------------- */
/*  ticket-create page                                                        */
/* -------------------------------------------------------------------------- */

function initTicketCreatePage(form) {
    initBootstrapValidation(form);

    const categorySelect = document.getElementById('categorySelect');
    const orderWrap = document.getElementById('orderSelectWrap');
    const orderSelect = document.getElementById('orderSelect');

    const subjectInput = document.getElementById('subjectInput');
    const bodyEl = document.getElementById('replyMessage');
    const submitBtn = document.getElementById('replySubmitBtn');

    const prefillCategory = (form.getAttribute('data-prefill-category') || '') === '1';
    const prefillOrder = (form.getAttribute('data-prefill-order') || '') === '1';

    function refreshOrderVisibility() {
        if (!categorySelect || !orderWrap || !orderSelect) return;

        // Prefill order akışında order alanı her zaman görünür + required olmalı.
        if (prefillOrder) {
            orderWrap.style.display = '';
            orderSelect.setAttribute('required', 'required');
            return;
        }

        const opt = categorySelect.options[categorySelect.selectedIndex];
        const requiresOrder = opt ? (opt.getAttribute('data-requires-order') === '1') : false;

        if (requiresOrder) {
            orderWrap.style.display = '';
            orderSelect.setAttribute('required', 'required');
        } else {
            orderWrap.style.display = 'none';
            orderSelect.removeAttribute('required');

            // order kilitli değilse temizle
            if (!orderSelect.disabled) {
                orderSelect.value = '';
            }

            orderSelect.classList.remove('is-invalid');
        }
    }

    if (categorySelect) {
        // kategori prefill ile kilitliyse change akışına bağlama
        if (!prefillCategory) {
            categorySelect.addEventListener('change', function () {
                refreshOrderVisibility();
                // submit state attachments controller içinde refresh edilecek ama garanti olsun:
                if (submitBtn) submitBtn.disabled = true;
            });
        }

        refreshOrderVisibility();
    }

    const isFormValidFn = () => {
        if (!categorySelect || String(categorySelect.value || '').trim() === '') return false;
        if (!subjectInput || String(subjectInput.value || '').trim() === '') return false;
        if (!bodyEl || String(bodyEl.value || '').trim() === '') return false;

        // prefill order akışında order zorunlu
        if (prefillOrder) {
            if (!orderSelect || String(orderSelect.value || '').trim() === '') return false;
        } else if (orderSelect && orderSelect.hasAttribute('required')) {
            if (String(orderSelect.value || '').trim() === '') return false;
        }

        return true;
    };

    initAttachmentsController({
        form,
        listEl: document.getElementById('attachmentsList'),
        addBtnEl: document.getElementById('addAttachmentBtn'),
        addBtnTextEl: document.getElementById('addAttachmentBtnText'),
        // create sayfasında hint/error box yok (istersen ekleriz), null kalması sorun değil
        hintEl: document.getElementById('attachmentsHint'),
        submitBtnEl: submitBtn,
        errorBoxEl: document.getElementById('attachmentsError'),
        isFormValidFn,
    });

    if (orderSelect && !prefillOrder) {
        orderSelect.addEventListener('change', function () {
            const opt = orderSelect.options[orderSelect.selectedIndex];
            const hasTicket = opt && opt.getAttribute('data-has-ticket') === '1';

            const hint = document.getElementById('orderTicketHint');

            if (hasTicket) {
                hint?.classList.remove('d-none');
                submitBtn.disabled = true;
            } else {
                hint?.classList.add('d-none');
            }
        });
    }
}
