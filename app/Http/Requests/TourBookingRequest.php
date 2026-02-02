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
            'tour_id'   => ['required', 'integer'],

            // display/name/label: server-derived (S0-1)
            'tour_name' => ['prohibited'],

            // strict: Y-m-d (civil date)
            'date'      => ['required', 'date_format:Y-m-d'],

            'adults'    => ['required', 'integer', 'min:1'],
            'children'  => ['nullable', 'integer', 'min:0'],
            'infants'   => ['nullable', 'integer', 'min:0'],

            // currency + price: server authoritative
            'currency'    => ['prohibited'],
            'price_total' => ['prohibited'],

            // snapshot display alanları: server-derived
            'cover_image'    => ['prohibited'],
            'category_name'  => ['prohibited'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'date' => $this->normalizeDateToYmd($this->input('date')),
        ]);
    }
}
