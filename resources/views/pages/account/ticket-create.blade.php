{{-- resources/views/pages/account/ticket-create.blade.php --}}
@extends('layouts.account')

@section('account_content')

    {{-- Başlık / meta (detail sayfasına benzer) --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="h5 mb-1">Yeni Destek Talebi</h2>
                    <div class="text-muted small">
                        Konu tipini seçip talebinizi oluşturabilirsiniz.
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <form action="{{ localized_route('account.tickets.store') }}"
                  method="post"
                  enctype="multipart/form-data"
                  id="ticketCreateForm"
                  class="needs-validation"
                  novalidate>
                @csrf

                <div class="row g-3">
                    {{-- Konu Tipi --}}
                    <div class="col-12 col-lg-6">
                        <label for="categorySelect" class="form-label">
                            Konu Tipi
                        </label>

                        <select id="categorySelect"
                                name="support_ticket_category_id"
                                class="form-select @error('support_ticket_category_id') is-invalid @enderror"
                                required>
                            <option value="">Seçiniz</option>

                            @foreach(($categories ?? []) as $cat)
                                <option value="{{ $cat->id }}"
                                        data-requires-order="{{ $cat->requires_order ? '1' : '0' }}"
                                    {{ (string) old('support_ticket_category_id') === (string) $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name_l }}
                                </option>
                            @endforeach
                        </select>

                        @error('support_ticket_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sipariş --}}
                    <div class="col-12 col-lg-6" id="orderSelectWrap" style="display:none;">
                        <label for="orderSelect" class="form-label">
                            Sipariş
                        </label>

                        @php
                            $selectedOrderId = old('order_id') ?? ($prefillOrderId ?? null);
                        @endphp

                        <select id="orderSelect"
                                name="order_id"
                                class="form-select @error('order_id') is-invalid @enderror">
                            <option value="">Seçiniz</option>

                            @foreach(($orders ?? []) as $order)
                                <option value="{{ $order->id }}"
                                        data-has-ticket="{{ $order->has_ticket ? '1' : '0' }}"
                                        @if($order->has_ticket) disabled @endif
                                    {{ (string) $selectedOrderId === (string) $order->id ? 'selected' : '' }}>
                                    {{ $order->code }}
                                    @if($order->has_ticket)
                                        — (Mevcut destek talebi var)
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('order_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konu Başlığı --}}
                    <div class="col-12">
                        <label for="subjectInput" class="form-label">
                            Konu Başlığı
                        </label>

                        <input type="text"
                               id="subjectInput"
                               name="subject"
                               value="{{ old('subject') }}"
                               class="form-control @error('subject') is-invalid @enderror"
                               maxlength="255"
                               required>

                        @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Mesaj + Ekler (detail sayfasıyla birebir aynı mantık) --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <div class="mb-3">
                        <textarea id="replyMessage"
                                  name="body"
                                  class="form-control @error('body') is-invalid @enderror"
                                  rows="5"
                                  placeholder="Mesajınızı yazın..."
                                  required>{{ old('body') }}</textarea>

                            @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dosya satırları --}}
                        <div class="mb-3">
                            <div id="attachmentsList" class="vstack gap-2"></div>

                            <button type="button"
                                    id="addAttachmentBtn"
                                    class="btn btn-secondary text-light btn-sm mt-3"
                                    data-text-add="Dosya Ekle"
                                    data-text-add-more="Başka Dosya Ekle">
                                <i class="fi-br-plus align-middle"></i>
                                <span id="addAttachmentBtnText">Dosya Ekle</span>
                            </button>

                            <span class="text-muted small d-block mt-2">
                            Dosya ekleri isteğe bağlıdır.
                        </span>

                            @error('attachments')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            @error('attachments.*')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <p class="mt-2 text-muted small">
                            <i class="fi-rs-shield-exclamation align-middle"></i>
                            <span>Kişisel bilgileriniz, hesap veya kredi kartı şifreniz gibi bilgileri kesinlikle paylaşmayın.</span>
                        </p>

                        <button id="replySubmitBtn" type="submit" class="btn btn-primary w-100 d-block d-lg-inline" disabled>
                            Talep Oluştur
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Geri bağlantısı --}}
    <div class="mt-4">
        <a href="{{ localized_route('account.tickets') }}" class="btn btn-outline-secondary d-block d-lg-inline">
            <i class="fi fi-rr-arrow-small-left align-middle me-1"></i>Geri dön
        </a>
    </div>

@endsection
