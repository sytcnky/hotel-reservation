<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TourBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Şimdilik herkese açık; ileride auth/role kontrolü eklenebilir
        return true;
    }

    public function rules(): array
    {
        return [
            'tour_id'     => ['required'],
            'tour_name'   => ['required', 'string'],
            'date'        => ['required', 'string'],
            'adults'      => ['required', 'integer', 'min:1'],
            'children'    => ['nullable', 'integer', 'min:0'],
            'infants'     => ['nullable', 'integer', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'price_total' => ['required', 'numeric', 'min:0'],
        ];
    }
}
