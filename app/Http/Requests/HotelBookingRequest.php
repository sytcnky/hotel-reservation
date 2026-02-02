<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesBookingSnapshot;
use Illuminate\Foundation\Http\FormRequest;

class HotelBookingRequest extends FormRequest
{
    use NormalizesBookingSnapshot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id'   => ['required', 'integer'],
            'room_id'    => ['required', 'integer'],

            // display/name/label alanları: server-derived (S0-1)
            'hotel_name'       => ['prohibited'],
            'room_name'        => ['prohibited'],
            'board_type_name'  => ['prohibited'],
            'location_label'   => ['prohibited'],

            // strict: Y-m-d (civil date)
            'checkin'    => ['required', 'date_format:Y-m-d'],
            'checkout'   => ['required', 'date_format:Y-m-d', 'after:checkin'],

            // nights: server authoritative (K5)
            'nights'     => ['prohibited'],

            'adults'     => ['required', 'integer', 'min:1'],
            'children'   => ['nullable', 'integer', 'min:0'],

            // currency + price: server authoritative
            'currency'    => ['prohibited'],
            'price_total' => ['prohibited'],

            // Opsiyonel (ID kabul edilir, name kabul edilmez)
            'board_type_id' => ['nullable', 'integer', 'min:1'],
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
