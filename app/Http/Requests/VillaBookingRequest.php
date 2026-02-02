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

            // display/name/label: server-derived (S0-1)
            'villa_name'     => ['prohibited'],
            'location_label' => ['prohibited'],

            // strict: Y-m-d (civil date)
            'checkin'    => ['required', 'date_format:Y-m-d'],
            'checkout'   => ['required', 'date_format:Y-m-d', 'after:checkin'],

            // nights: server authoritative (K5)
            'nights'     => ['prohibited'],

            'adults'     => ['required', 'integer', 'min:1'],
            'children'   => ['nullable', 'integer', 'min:0'],

            // currency + price: server authoritative
            'currency'         => ['prohibited'],
            'price_nightly'    => ['prohibited'],
            'price_prepayment' => ['prohibited'],
            'price_total'      => ['prohibited'],

            // snapshot image alanı: server-derived
            'villa_image' => ['prohibited'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'checkin'  => $this->normalizeDateToYmd($this->input('checkin')),
            'checkout' => $this->normalizeDateToYmd($this->input('checkout')),
        ]);
    }
}
