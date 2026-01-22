<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesBookingSnapshot;
use Illuminate\Foundation\Http\FormRequest;

class VillaBookingRequest extends FormRequest
{
    use NormalizesBookingSnapshot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'villa_id'   => ['required', 'integer'],
            'villa_name' => ['required', 'string'],

            // strict: Y-m-d (civil date)
            'checkin'    => ['required', 'date_format:Y-m-d'],
            'checkout'   => ['required', 'date_format:Y-m-d', 'after:checkin'],

            // nights yine taşınabilir ama server authoritative hesaplayacak (tamperable)
            'nights'     => ['required', 'integer', 'min:1'],

            'adults'     => ['required', 'integer', 'min:1'],
            'children'   => ['nullable', 'integer', 'min:0'],

            // Currency + price alanları client’tan gelsin/gelişmesin: authoritative DEĞİL.
            // Server, CurrencyContext + Villa rateRules ile hesaplayıp override edecek.
            'currency'         => ['nullable', 'string', 'size:3'],
            'price_nightly'    => ['nullable', 'numeric', 'min:0'],
            'price_prepayment' => ['nullable', 'numeric', 'min:0'],
            'price_total'      => ['nullable', 'numeric', 'min:0'],

            // opsiyoneller
            'location_label'   => ['nullable', 'string'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'checkin'  => $this->normalizeDateToYmd($this->input('checkin')),
            'checkout' => $this->normalizeDateToYmd($this->input('checkout')),

            // currency normalize sadece taşınıyorsa; authoritative değil
            'currency' => $this->normalizeCurrency($this->input('currency')),
        ]);
    }
}
