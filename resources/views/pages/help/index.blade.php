@extends('layouts.app', ['pageKey' => 'help'])
@section('title', 'Yardım & SSS')
@section('content')
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
                        <input type="search" class="form-control" id="faqSearch" placeholder="Ara: ödeme, iptal, fatura..." aria-label="SSS içinde ara">
                        <button class="btn btn-outline-secondary d-none" type="button" id="faqClear">Temizle</button>
                    </div>

                    <!-- Sonuç yok mesajı -->
                    <div id="faqEmpty" class="alert alert-light border d-none" role="status">
                        Aramanızla eşleşen sonuç bulunamadı.
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
                                Hâlâ yardıma mı ihtiyacın var?
                            </div>
                            <div class="d-flex gap-2">

                                <a href="https://wa.me/905551112233" target="_blank"
                                   class="btn btn-outline-success btn-sm text-decoration-none">
                                    <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
                                    <span>Whatsapp Destek</span>
                                </a>
                                <a href="{{ localized_route('contact') }}" class="btn btn-outline-primary">
                                    Bize Ulaşın
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
