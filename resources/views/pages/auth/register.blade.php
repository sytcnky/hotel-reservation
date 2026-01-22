{{-- resources/views/pages/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', t('auth.register.title'))

@section('content')
    <script>
        window.intlTelInputI18n = {
            searchPlaceholder: "{{ e(t('intl_tel.search_placeholder')) }}",
            zeroSearchResults: "{{ e(t('intl_tel.zero_results')) }}",
            clearSearch: "{{ e(t('intl_tel.clear_search')) }}",
        };
        window.phone_required = "{{ e(t('intl_tel.phone_required')) }}";
        window.phone_invalid  = "{{ e(t('intl_tel.phone_invalid')) }}";
    </script>

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-5">

                <h1 class="mb-4 text-secondary">{{ t('auth.register.heading') }}</h1>

                <form method="POST" action="{{ route('register.store') }}" class="needs-validation" novalidate>
                    @csrf

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ t('auth.register.first_name.label') }}</label>
                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ t('auth.register.last_name.label') }}</label>
                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-2">
                        <label class="form-label">{{ t('auth.register.phone.label') }}</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror"
                            data-phone-input
                            required
                        >

                        <div class="invalid-feedback d-block phone-error"
                             @if($errors->has('phone')) style="display:block" @else style="display:none" @endif>
                            @error('phone'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ t('auth.register.email.label') }}</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ t('auth.register.password.label') }}</label>
                        <input type="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ t('auth.register.password_confirm.label') }}</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        {{ t('auth.register.actions.submit') }}
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">
                            {{ t('auth.register.have_account') }}
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </section>
@endsection
