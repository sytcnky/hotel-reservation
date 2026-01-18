<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesBookingSnapshot;
use Illuminate\Foundation\Http\FormRequest;

class TourBookingRequest extends FormRequest
{
    use NormalizesBookingSnapshot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tour_id'     => ['required'],
            'tour_name'   => ['required', 'string'],

            // strict: Y-m-d
            'date'        => ['required', 'date_format:Y-m-d'],

            'adults'      => ['required', 'integer', 'min:1'],
            'children'    => ['nullable', 'integer', 'min:0'],
            'infants'     => ['nullable', 'integer', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
            'price_total' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'date'     => $this->normalizeDateToYmd($this->input('date')),
            'currency' => $this->normalizeCurrency($this->input('currency')),
        ]);
    }
}
