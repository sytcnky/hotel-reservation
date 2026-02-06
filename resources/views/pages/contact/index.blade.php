{{-- resources/views/pages/contact/index.blade.php --}}
@extends('layouts.app')

@section('title', 'İletişim')

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
                $page = \App\Models\StaticPage::where('key','contact_page')->where('is_active',true)->first();
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

                <div class="col-lg-8 mb-3">
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    @php
                        $offices = $c['offices'] ?? [];
                    @endphp

                    <div class="row">
                        @foreach($offices as $office)
                            @php
                                $officeName = $office['name'][$loc] ?? '';
                                $address = $office['address'][$loc] ?? '';
                                $workingHours = $office['working_hours'][$loc] ?? '';

                                $mapUrl = $office['map_embed_url'] ?? '';
                                $phone = $office['phone'] ?? '';
                                $email = $office['email'] ?? '';
                            @endphp

                            <div class="col-lg-6 mb-5">
                                <div class="card">
                                    <div class="card-body small">
                                        @if($mapUrl)
                                            <div class="ratio ratio-21x9 rounded overflow-hidden shadow-sm mb-3">
                                                <iframe
                                                    src="{{ $mapUrl }}"
                                                    allowfullscreen
                                                    loading="lazy"
                                                    referrerpolicy="no-referrer-when-downgrade"
                                                    title="Harita">
                                                </iframe>
                                            </div>
                                        @endif

                                        <h5 class="m-0">{{ $officeName }}</h5>
                                        <hr>

                                        <div class="d-flex align-items-start mb-3">
                                            <i class="fi fi-rr-marker me-2 fs-4 text-secondary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ t('ui.contact.address') }}</div>
                                                <div class="text-muted">{{ $address }}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start mb-3">
                                            <i class="fi fi-rr-phone-call me-2 fs-4 text-secondary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ t('ui.contact.phone') }}</div>
                                                @if($phone)
                                                    <a href="tel:{{ $phone }}">{{ $phone }}</a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start mb-3">
                                            <i class="fi fi-rr-envelope me-2 fs-4 text-secondary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ t('ui.contact.email') }}</div>
                                                @if($email)
                                                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start">
                                            <i class="fi fi-rr-clock-three me-2 fs-4 text-secondary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ t('ui.contact.opening_hours') }}</div>
                                                <div class="text-muted">{{ $workingHours }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- İletişim formu --}}
                <!--<div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="mb-3">İletişim Formu</h5>

                            <form action="#" method="POST" class="needs-validation" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Ad</label>
                                        <input type="text" name="first_name" class="form-control" required autocomplete="given-name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Soyad</label>
                                        <input type="text" name="last_name" class="form-control" required autocomplete="family-name">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" name="email" class="form-control" required autocomplete="email" inputmode="email" placeholder="ornek@mail.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telefon</label>
                                        <input type="tel" name="phone" class="form-control" autocomplete="tel" inputmode="tel" placeholder="+90 5xx xxx xx xx">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Konu</label>
                                        <input type="text" name="subject" class="form-control" required placeholder="Örn. Rezervasyon hakkında">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Mesajınız</label>
                                        <textarea name="message" class="form-control" rows="5" required placeholder="Size nasıl yardımcı olabiliriz?"></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="kvkk" required>
                                            <label class="form-check-label small" for="kvkk">
                                                KVKK ve gizlilik metnini okudum, kabul ediyorum.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Mesajı Gönder
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
    </section>
@endsection
