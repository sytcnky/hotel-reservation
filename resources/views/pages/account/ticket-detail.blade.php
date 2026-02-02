@extends('layouts.account', ['pageKey' => 'account-tickets'])
@section('account_content')

    {{-- Başlık / meta --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $ticket->subject }}</h2>
                    <div class="text-muted small">
                        {{ $ticket->category?->name_l ?? '-' }} — {{ t('account.tickets.meta.no') }}: {{ $ticket->id }}
                    </div>

                    @if($ticket->status === 'waiting_agent')
                        <span class="badge bg-warning-subtle text-warning mt-3 d-lg-none">
                            {{ t('account.tickets.status.waiting') }}
                        </span>
                    @elseif($ticket->status === 'waiting_customer')
                        <span class="badge bg-success-subtle text-success mt-3 d-lg-none">
                            {{ t('account.tickets.status.answered') }}
                        </span>
                    @elseif($ticket->status === 'closed')
                        <span class="badge bg-secondary-subtle text-secondary mt-3 d-lg-none">
                            {{ t('account.tickets.status.closed') }}
                        </span>
                    @endif
                </div>

                @if($ticket->status === 'waiting_agent')
                    <span class="badge bg-warning-subtle text-warning d-none d-lg-block">
                        {{ t('account.tickets.status.waiting') }}
                    </span>
                @elseif($ticket->status === 'waiting_customer')
                    <span class="badge bg-success-subtle text-success d-none d-lg-block">
                        {{ t('account.tickets.status.answered') }}
                    </span>
                @elseif($ticket->status === 'closed')
                    <span class="badge bg-secondary-subtle text-secondary d-none d-lg-block">
                        {{ t('account.tickets.status.closed') }}
                    </span>
                @endif
            </div>

            <hr class="my-3">

            <dl class="row mb-0 small text-muted">
                <dt class="col-sm-3">{{ t('account.tickets.meta.created') }}</dt>
                <dd class="col-sm-9">
                    {{ \App\Support\Date\DatePresenter::humanDateTime($ticket->created_at) }}
                </dd>

                <dt class="col-sm-3">{{ t('account.tickets.meta.updated') }}</dt>
                <dd class="col-sm-9">
                    {{ \App\Support\Date\DatePresenter::humanDateTime($ticket->last_message_at) }}
                </dd>

                @if(!empty($ticket->order_id))
                    <dt class="col-sm-3">{{ t('account.tickets.meta.order') }}</dt>
                    <dd class="col-sm-9">
                        {{ $ticket->order?->code ?? $ticket->order_id }}
                    </dd>
                @endif
            </dl>
        </div>
    </div>

    {{-- Mesaj akışı --}}
    <div class="vstack gap-3">
        @forelse(($ticket->messages ?? []) as $message)
            @php
                $isAgent = (($message->author_type ?? null) === 'agent');
                $authorName = $message->author?->name ?? ($isAgent ? t('account.tickets.author.support') : t('account.tickets.author.user'));
            @endphp

            <div class="card shadow-sm {{ $isAgent ? 'bg-info bg-opacity-10' : 'bg-light' }}">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-lg-center mb-1">
                        <div class="fw-semibold d-flex align-items-center">
                            <span class="me-2">{{ $authorName }}</span>
                            @if($isAgent)
                                <span class="badge bg-info-subtle text-info-emphasis">
                                    {{ t('account.tickets.author.support') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ \App\Support\Date\DatePresenter::humanDateTime($message->created_at) }}
                        </div>
                    </div>

                    <hr>

                    <p class="mb-0">{{ $message->body }}</p>

                    @php
                        $attachments = method_exists($message, 'getMedia') ? $message->getMedia('attachments') : collect();
                    @endphp

                    @if($attachments->count() > 0)
                        <div class="d-block">
                            <hr>
                            @foreach($attachments as $media)
                                <div>
                                    <i class="fi fi-rr-clip align-middle"></i>
                                    <a href="{{ $media->getUrl() }}" class="btn-link small" target="_blank" rel="noopener">
                                        {{ $media->file_name }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-muted">
                    {{ t('account.tickets.messages.empty') }}
                </div>
            </div>
        @endforelse
    </div>

    {{-- Yanıt formu --}}
    @if($ticket->status !== 'closed')
        <div class="card shadow-sm mt-5">
            <div class="card-body">

                @php
                    $serverAttachmentError = $errors->first('attachments') ?: $errors->first('attachments.*');
                @endphp

                <form id="replyForm"
                      action="{{ localized_route('account.tickets.message', ['ticket' => $ticket->id]) }}"
                      method="post"
                      enctype="multipart/form-data"
                      class="needs-validation"
                      novalidate>
                    @csrf

                    {{-- Body --}}
                    <div class="mb-3">
                        <textarea id="replyMessage"
                                  name="body"
                                  class="form-control @error('body') is-invalid @enderror"
                                  rows="4"
                                  placeholder="{{ t('account.tickets.reply.placeholder') }}"
                                  required>{{ old('body') }}</textarea>

                        <div class="invalid-feedback">
                            {{ t('account.tickets.reply.required') }}
                        </div>

                        @error('body')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Attachments --}}
                    <div class="mb-3">
                        <div id="attachmentsList" class="vstack gap-2"></div>

                        <div
                            id="attachmentsError"
                            class="alert alert-danger py-2 px-3 mt-2 mb-0 {{ ($serverAttachmentError) ? '' : 'd-none' }}"
                            role="alert"
                        >
                            {{ $serverAttachmentError }}
                        </div>

                        <button type="button"
                                id="addAttachmentBtn"
                                class="btn btn-secondary text-light btn-sm mt-3"
                                data-text-add="{{ t('account.tickets.attachments.add') }}"
                                data-text-add-more="{{ t('account.tickets.attachments.add_more') }}"
                                data-msg-file-too-large="{{ t('msg.err.account.tickets.attachments.errors.too_large') }}"
                                data-msg-file-type-unsupported="{{ t('msg.err.account.tickets.attachments.errors.type_unsupported') }}"
                                data-msg-file-invalid-generic="{{ t('msg.err.account.tickets.attachments.errors.invalid_generic') }}">
                            <i class="fi-br-plus align-middle"></i>
                            <span id="addAttachmentBtnText">
                                {{ t('account.tickets.attachments.add') }}
                            </span>
                        </button>

                        <p id="attachmentsHint" class="small text-muted d-none mb-0 mt-2">
                            {{ t('account.tickets.attachments.hint') }}
                        </p>
                    </div>

                    <p class="mt-2 text-muted small">
                        <i class="fi-rs-shield-exclamation align-middle"></i>
                        <span>{{ t('account.tickets.security_notice') }}</span>
                    </p>

                    <button id="replySubmitBtn"
                            type="submit"
                            class="btn btn-primary w-100 d-block d-lg-inline"
                            disabled>
                        {{ t('account.tickets.action.reply') }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Geri bağlantısı --}}
    <div class="mt-4">
        <a href="{{ localized_route('account.tickets') }}" class="btn btn-outline-secondary d-block d-lg-inline">
            <i class="fi fi-rr-arrow-small-left align-middle me-1"></i>{{ t('account.tickets.action.back') }}
        </a>
    </div>

@endsection
