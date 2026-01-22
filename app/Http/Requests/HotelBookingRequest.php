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
            'hotel_name' => ['required', 'string'],
            'room_id'    => ['required', 'integer'],
            'room_name'  => ['required', 'string'],

            // strict: Y-m-d (civil date)
            'checkin'    => ['required', 'date_format:Y-m-d'],
            'checkout'   => ['required', 'date_format:Y-m-d', 'after:checkin'],

            'nights'     => ['required', 'integer', 'min:1'],
            'adults'     => ['required', 'integer', 'min:1'],
            'children'   => ['nullable', 'integer', 'min:0'],

            // Currency + price alanları client’tan gelebilir ama authoritative DEĞİL
            'currency'    => ['nullable', 'string', 'size:3'],
            'price_total' => ['nullable', 'numeric', 'min:0'],

            // Opsiyonel
            'board_type_id' => ['nullable', 'integer', 'min:1'],
            'board_type_name' => ['nullable', 'string'],
            'location_label' => ['nullable', 'string'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'checkin'  => $this->normalizeDateToYmd($this->input('checkin')),
            'checkout' => $this->normalizeDateToYmd($this->input('checkout')),

            // normalize edilir ama authoritative değildir
            'currency' => $this->normalizeCurrency($this->input('currency')),
        ]);
    }
}
