@extends('layouts.app', ['pageKey' => 'help'])
@section('title', 'Yardım & SSS')
@section('content')
    @php
        use App\Support\Helpers\LocaleHelper;
        use App\Models\Setting;

        $locale      = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $pick = function (string $key) use ($locale, $baseLocale): ?string {
            $map = Setting::get($key, []);
            if (! is_array($map)) {
                return null;
            }

            // kontrat: ui → base
            $val = $map[$locale] ?? $map[$baseLocale] ?? null;

            return is_string($val) && trim($val) !== '' ? trim($val) : null;
        };

        $waLabel = $pick('contact_whatsapp_label');
        $waPhone = $pick('contact_whatsapp_phone');

        $waNumber = $waPhone ? preg_replace('/[^0-9]/', '', $waPhone) : null;
        $waUrl    = $waNumber ? ('https://wa.me/' . $waNumber) : null;
    @endphp

        <section>
            <div class="text-center my-5 px-lg-5">
                @php
                    $page = \App\Models\StaticPage::where('key','help_page')->where('is_active',true)->first();
                    $c    = $page->content ?? [];
                    $loc  = app()->getLocale();
            @endphp

            <h1 class="display-5 fw-bold text-secondary">
                {{ $c['page_header']['title'][$loc] ?? '' }}
            </h1>

            <p class="lead text-muted px-lg-5">
                {{ $c['page_header']['description'][$loc] ?? '' }}
            </p>
        </div>
    </section>
    <section class="pb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Arama -->
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="fi fi-rr-search"></i></span>
                        <input type="search" class="form-control" id="faqSearch" placeholder="{{ t('help.search_placeholder') }}" aria-label="{{ t('help.search_placeholder') }}">
                        <button class="btn btn-outline-secondary d-none" type="button" id="faqClear">Clear</button>
                    </div>

                    <!-- Sonuç yok mesajı -->
                    <div id="faqEmpty" class="alert alert-light border d-none" role="status">
                        {{ t('help.no_result') }}
                    </div>


                    @php
                        $items = $c['faq_items'] ?? [];
                    @endphp

                    <div class="accordion" id="faqAccordion">
                        @foreach($items as $i => $item)
                            @php
                                $q = $item['question'][$loc] ?? '';
                                $a = $item['answer'][$loc] ?? '';
                                $qid = 'q'.$i;
                                $aid = 'a'.$i;
                            @endphp

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="{{ $qid }}">
                                    <button class="accordion-button {{ $i ? 'collapsed' : '' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $aid }}"
                                            aria-expanded="{{ $i ? 'false' : 'true' }}"
                                            aria-controls="{{ $aid }}">
                                        {{ $q }}
                                    </button>
                                </h2>
                                <div id="{{ $aid }}"
                                     class="accordion-collapse collapse {{ $i ? '' : 'show' }}"
                                     aria-labelledby="{{ $qid }}"
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        {{ $a }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Alt CTA --}}
                    <div class="card bg-light border-0 mt-4">
                        <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <div class="text-muted mb-2 mb-md-0">
                                {{ t('help.need_more_help') }}
                            </div>
                            <div class="d-flex gap-2">
                                @if ($waUrl && $waLabel)
                                    <a href="{{ $waUrl }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="btn btn-outline-success btn-sm text-decoration-none">
                                        <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
                                        <span>{{ $waLabel }}</span>
                                    </a>
                                @endif
                                <a href="{{ localized_route('contact') }}" class="btn btn-sm btn-outline-primary">
                                    {{ t('nav.contact') }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
