@php
    use App\Models\Setting;
    use App\Support\Helpers\LocaleHelper;

    $uiLocale   = app()->getLocale();
    $baseLocale = LocaleHelper::defaultCode();

    // i18n map (['tr' => '...', 'en' => '...']) -> ui → base → (edge-case) en
    $pick = function (string $key) use ($uiLocale, $baseLocale): ?string {
        $map = Setting::get($key, []);
        if (! is_array($map)) {
            return null;
        }

        $val = $map[$uiLocale] ?? $map[$baseLocale] ?? $map['en'] ?? null;
        if (! is_string($val)) {
            return null;
        }

        $val = trim($val);
        return $val !== '' ? $val : null;
    };

    // URL normalize (Filament prefix karmaşasına karşı)
    $normalizeUrl = function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return 'https://' . ltrim($url, '/');
    };

    // tel: href
    $toTelHref = function (?string $phone): ?string {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }
        $href = preg_replace('/[^0-9\+]/', '', $raw) ?? '';
        return $href !== '' ? ('tel:' . $href) : null;
    };

    $year = (int) date('Y');

    $shortDesc = $pick('footer_short_description');

    $office1Label = $pick('contact_office1_label');
    $office1Phone = $pick('contact_office1_phone');
    $office1Tel   = $toTelHref($office1Phone);

    $office2Label = $pick('contact_office2_label');
    $office2Phone = $pick('contact_office2_phone');
    $office2Tel   = $toTelHref($office2Phone);

    $facebookUrl  = $normalizeUrl(Setting::get('facebook', ''));
    $youtubeUrl   = $normalizeUrl(Setting::get('youtube', ''));
    $instagramUrl = $normalizeUrl(Setting::get('instagram', ''));

    $copyright = $pick('footer_copyright');
@endphp


<footer class="bg-light-subtle py-4">
    <div class="container">
        <div class="row pt-3">
            <!-- Logo ve Hakkında -->
            <div class="col-xl-3 mb-4">
                <a href="/" class="text-primary text-decoration-none fw-bold fs-2 d-block mb-2">
                    <span class="text-primary">Icmeler</span><span class="text-warning">Online</span>
                </a>

                @if (!empty($shortDesc))
                    <p class="small fst-italic">{{ $shortDesc }}</p>
                @endif

                <div class="small align-items-center">
                    <img src="/images/tursab-logo.png" width="96">
                </div>
            </div>

            <!-- Menü -->
            <div class="col-xl-2 col-6 mb-4 d-none d-lg-block">
                <ul class="list-unstyled">
                    <li>
                        <a class="text-decoration-none"
                           href="{{ localized_route('hotels') }}">{{ t('nav.hotels') }}</a>
                    </li>
                    <li><a class="text-decoration-none" href="{{ localized_route('transfers') }}">{{ t('nav.transfers') }}</a></li>
                    <li><a class="text-decoration-none" href="{{ localized_route('villa') }}">{{ t('nav.villas') }}</a></li>
                    <li><a class="text-decoration-none" href="{{ localized_route('excursions') }}">{{ t('nav.excursions') }}</a></li>
                </ul>
            </div>

            <!-- Site -->
            <div class="col-xl-2 col-6 mb-4 d-none d-lg-block">
                <ul class="list-unstyled">
                    <li><a href="{{ localized_route('home') }}" class="text-decoration-none">{{ t('nav.home') }}</a></li>
                    <li><a class="text-decoration-none" href="{{ localized_route('guides') }}">{{ t('nav.guides') }}</a></li>
                    <li><a href="{{ localized_route('help') }}" class="text-decoration-none">{{ t('nav.help') }}</a></li>
                    <li><a href="{{ localized_route('contact') }}" class="text-decoration-none">{{ t('nav.contact') }}</a></li>
                </ul>
            </div>

            <!-- Sosyal Medya + İletişim -->
            <div class="col-xl-5 mb-4">
                <div class="d-xl-flex gap-3 justify-content-between">
                    @if (!empty($office1Label) && !empty($office1Phone))
                        <a href="{{ $office1Tel ?: '#' }}" class="mb-3 d-flex gap-3 align-items-center text-decoration-none">
                            <div class="border border-primary-subtle text-primary p-2 rounded">
                                <i class="fi fi-rr-phone-flip fs-4"></i>
                            </div>
                            <div class="small text-secondary">
                                {{ $office1Label }}
                                <h6>{{ $office1Phone }}</h6>
                            </div>
                        </a>
                    @endif

                    @if (!empty($office2Label) && !empty($office2Phone))
                        <a href="{{ $office2Tel ?: '#' }}" class="mb-3 d-flex gap-3 align-items-center text-decoration-none">
                            <div class="border border-primary-subtle text-primary p-2 rounded">
                                <i class="fi fi-rr-phone-flip fs-4"></i>
                            </div>
                            <div class="small text-secondary">
                                {{ $office2Label }}
                                <h6>{{ $office2Phone }}</h6>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="d-flex align-items-center justify-content-between gap-3">
                    @if (!empty($facebookUrl))
                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener"
                           class="border p-2 border-primary-subtle rounded text-center text-decoration-none mb-1 small flex-fill">
                            <i class="fi fi-brands-facebook fs-2 align-middle"></i>
                            <div class="opacity-50 d-none d-xl-block">Facebook</div>
                            <div class="d-none d-xl-block">@icmeleronline</div>
                        </a>
                    @endif

                    @if (!empty($youtubeUrl))
                        <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener"
                           class="border p-2 border-danger-subtle rounded text-center text-danger text-decoration-none mb-1 small flex-fill">
                            <i class="fi fi-brands-youtube fs-2 align-middle"></i>
                            <div class="opacity-50 d-none d-xl-block">Youtube</div>
                            <div class="d-none d-xl-block">@icmeleronline</div>
                        </a>
                    @endif

                    @if (!empty($instagramUrl))
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener"
                           class="border p-2 border-warning rounded text-center text-warning text-decoration-none mb-1 small flex-fill">
                            <i class="fi fi-brands-instagram fs-2 align-middle"></i>
                            <div class="opacity-50 d-none d-xl-block">Instagram</div>
                            <div class="d-none d-xl-block">@icmeleronline</div>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <hr>
        <div class="row">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
                <div class="text-center text-md-start mb-2 mb-md-0">
                    © {{ $year }} {{ $copyright }}
                </div>
                <div class="d-flex gap-3">
                    <a href="{{ localized_route('privacy_policy') }}" class="text-decoration-none">{{ t('nav.privacy_policy') }}</a>
                    <a href="{{ localized_route('distance_sales') }}" class="text-decoration-none">{{ t('nav.distance_sales') }}</a>
                    <a href="{{ localized_route('terms_of_use') }}" class="text-decoration-none">{{ t('nav.terms_of_use') }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
