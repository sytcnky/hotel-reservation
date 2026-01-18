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
            'villa_id'         => ['required', 'integer'],
            'villa_name'       => ['required', 'string'],

            // strict: Y-m-d
            'checkin'          => ['required', 'date_format:Y-m-d'],
            'checkout'         => ['required', 'date_format:Y-m-d', 'after:checkin'],

            'nights'           => ['required', 'integer', 'min:1'],
            'adults'           => ['required', 'integer', 'min:1'],
            'children'         => ['nullable', 'integer', 'min:0'],
            'currency'         => ['required', 'string', 'size:3'],
            'price_nightly'    => ['required', 'numeric', 'min:0'],
            'price_prepayment' => ['required', 'numeric', 'min:0'],
            'price_total'      => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'checkin'  => $this->normalizeDateToYmd($this->input('checkin')),
            'checkout' => $this->normalizeDateToYmd($this->input('checkout')),
            'currency' => $this->normalizeCurrency($this->input('currency')),
        ]);
    }
}
