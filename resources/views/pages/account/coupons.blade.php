@extends('layouts.account')

@section('account_content')
    @php
        /** @var array[] $activeCoupons */
        /** @var array[] $pastCoupons */

        $activeCoupons = $activeCoupons ?? [];
        $pastCoupons   = $pastCoupons ?? [];
    @endphp

    {{-- Kupon text + tooltip helper'ları --}}
    @include('partials.coupons.helpers')

    <div class="row">
        <div class="col-12">
            <h4 class="mb-3 h6">Aktif Kuponlarım</h4>
        </div>

        @forelse($activeCoupons as $coupon)
            @php
                $texts       = coupon_build_texts($coupon);
                $tooltipHtml = coupon_build_tooltip_html($coupon);
                $badgeLabel  = $coupon['badge_label'] ?? '';
            @endphp

            <div class="col-12 col-lg-6 mb-4">
                <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                    <div class="coupon-badge border me-2 text-center px-2 py-1">
                        <div class="h2 fw-bolder text-primary mb-0">
                            {{ $coupon['badge_main'] }}
                        </div>
                        <div class="badge text-bg-primary">
                            {{ $badgeLabel }}
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <div class="small fw-semibold d-flex align-items-center">
                            <span>{{ $coupon['title'] ?: $coupon['code'] }}</span>

                            {{-- Info ikonu + HTML tooltip --}}
                            <button type="button"
                                    class="btn btn-link btn-sm text-muted p-0 ms-2"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    data-bs-custom-class="coupon-tooltip"
                                    title="{!! $tooltipHtml !!}">
                                <i class="fi fi-rr-info" aria-hidden="true"></i>
                                <span class="visually-hidden">Detay</span>
                            </button>
                        </div>

                        <div class="small text-muted">
                            {{ $texts['alt_limit'] }}
                        </div>

                        <div class="small text-muted">
                            {{ $texts['validity_text'] }}
                        </div>

                        <div class="small text-muted">
                            {{ $texts['remaining'] }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border">
                    Şu anda aktif kuponunuz bulunmamaktadır.
                </div>
            </div>
        @endforelse
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-3 h6">Geçmiş Kuponlarım</h4>
        </div>

        @forelse($pastCoupons as $coupon)
            @php
                $texts       = coupon_build_texts($coupon);
                $tooltipHtml = coupon_build_tooltip_html($coupon);
                $statusLabel = $texts['status_label'];
            @endphp

            <div class="col-12 col-lg-6 mb-4">
                <div class="coupon-card mb-3 border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                    <div class="coupon-badge border me-2 text-center px-2 py-1">
                        <div class="h2 fw-bolder text-primary mb-0">
                            {{ $coupon['badge_main'] }}
                        </div>
                        <div class="badge text-bg-secondary">
                            {{ $statusLabel }}
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <div class="small fw-semibold d-flex align-items-center">
                            <span>{{ $coupon['title'] ?: $coupon['code'] }}</span>

                            <button type="button"
                                    class="btn btn-link btn-sm text-muted p-0 ms-2"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    data-bs-custom-class="coupon-tooltip"
                                    title="{!! $tooltipHtml !!}">
                                <i class="fi fi-rr-info" aria-hidden="true"></i>
                                <span class="visually-hidden">Detay</span>
                            </button>
                        </div>

                        <div class="small text-muted">
                            {{ $texts['alt_limit'] }}
                        </div>

                        <div class="small text-muted">
                            {{ $texts['validity_text'] }}
                        </div>

                        <div class="small text-muted">
                            {{ $texts['remaining'] }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border">
                    Geçmiş kupon kaydınız bulunmamaktadır.
                </div>
            </div>
        @endforelse
    </div>
@endsection
