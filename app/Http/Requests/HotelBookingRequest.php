<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id'        => ['required', 'integer'],
            'hotel_name'      => ['required', 'string'],
            'room_id'         => ['required', 'integer'],
            'room_name'       => ['required', 'string'],
            'checkin'         => ['required', 'date'],
            'checkout'        => ['required', 'date', 'after:checkin'],
            'nights'          => ['required', 'integer', 'min:1'],
            'adults'          => ['required', 'integer', 'min:1'],
            'children'        => ['nullable', 'integer', 'min:0'],
            'currency'        => ['required', 'string', 'size:3'],
            'price_total'     => ['required', 'numeric', 'min:0'],

            // Board type bazı otellerde var, bazılarında yok → nullable
            'board_type_name' => ['nullable', 'string'],
        ];
    }
}
