{{-- resources/views/forms/components/icon-picker.blade.php --}}
@php
$statePath = $getStatePath();
$cols      = $getGridColumns();
$withLbl   = $getWithLabels();
$variant   = $getVariant();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
      value: @entangle($statePath),
      q: '',
      cols: {{ $cols }},
      withLabels: {{ $withLbl ? 'true' : 'false' }},
      variant: '{{ $variant }}',
      icons: [],
      limit: 40,

      ensureStore(){ window.__uiconsStore ??= { data:{}, status:{} }; },

      async onOpen(){
        this.ensureStore();
        const key = `uicons:v1:${this.variant}`;

        if (this.icons.length) return;
        if (window.__uiconsStore.data[key]) { this.icons = window.__uiconsStore.data[key]; return; }

        if (window.__uiconsStore.status[key] !== 'pending') {
          window.__uiconsStore.status[key] = 'pending';
          try {
            const res = await fetch(`/admin/uicons?variant=${encodeURIComponent(this.variant)}`);
            const list = await res.json();
            window.__uiconsStore.data[key] = list;
            window.__uiconsStore.status[key] = 'done';
            this.icons = list;
            document.dispatchEvent(new CustomEvent('uicons:ready', { detail:{ key } }));
          } catch {
            window.__uiconsStore.status[key] = 'error';
          }
        } else {
          const h = (e)=>{ if (e.detail?.key === key) { this.icons = window.__uiconsStore.data[key] || []; document.removeEventListener('uicons:ready', h); } };
          document.addEventListener('uicons:ready', h);
        }
      },

      filtered(){ const k=this.q.toLowerCase(); return k ? this.icons.filter(c=>c.toLowerCase().includes(k)) : this.icons },
      visible(){ return this.filtered().slice(0, this.limit) },
      select(cls){ this.value = cls; },
      onScroll(e){ const el=e.target; if(el.scrollTop+el.clientHeight>=el.scrollHeight-48) this.limit += 20; },
    }"
    >
        <x-filament::dropdown placement="bottom-start" teleport :offset="8" :shift="true" :close-on-click="true">
            <x-slot name="trigger">
                <x-filament::button tag="button" size="sm" color="gray" class="fi-icon-trigger" aria-label="İkon seç"
                                    x-on:click="onOpen()">
                    <i :class="value || 'fi fi-rr-plus-small'" class="fi-btn-icon"></i>
                </x-filament::button>
            </x-slot>

            <x-filament::dropdown.list class="icon-dd"
                                       x-init="$nextTick(()=>{ $el.parentElement && ($el.parentElement.style.width='min(28rem, calc(100vw - 2rem))') })">
                <div class="icon-dd__section">
                    <input type="text" placeholder="Ara…" class="icon-search" x-model.debounce.150ms="q" />
                    <div class="icon-grid cols-6" @scroll.passive="onScroll($event)">
                        <template x-for="cls in visible()" :key="cls">
                            <button type="button" class="icon-option" @click="select(cls)">
                                <i :class="cls"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
</x-dynamic-component>
