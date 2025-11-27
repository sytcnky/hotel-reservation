<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VillaBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'villa_id'         => ['required', 'integer'],
            'villa_name'       => ['required', 'string'],
            'checkin'          => ['required', 'date'],
            'checkout'         => ['required', 'date', 'after:checkin'],
            'nights'           => ['required', 'integer', 'min:1'],
            'adults'           => ['required', 'integer', 'min:1'],
            'children'         => ['nullable', 'integer', 'min:0'],
            'currency'         => ['required', 'string', 'size:3'],
            'price_nightly'    => ['required', 'numeric', 'min:0'],
            'price_prepayment' => ['required', 'numeric', 'min:0'],
            'price_total'      => ['required', 'numeric', 'min:0'],
        ];
    }
}
