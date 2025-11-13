<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'route_id'          => ['required','integer'],
            'vehicle_id'        => ['required','integer'],
            'direction'         => ['required','in:oneway,roundtrip'],
            'from_location_id'  => ['required','integer','different:to_location_id'],
            'to_location_id'    => ['required','integer','different:from_location_id'],
            'departure_date'    => ['required','date'],
            'return_date'       => ['required_if:direction,roundtrip','date','nullable'],

            // Çift kuralı: OUTBOUND
            'pickup_time_outbound'     => ['nullable','date_format:H:i','required_without:flight_number_outbound'],
            'flight_number_outbound'   => ['nullable','string','max:20','required_without:pickup_time_outbound'],

            // Çift kuralı: RETURN (sadece roundtrip’te)
            'pickup_time_return'       => ['nullable','date_format:H:i','required_with:flight_number_return','required_if:direction,roundtrip','prohibited_unless:direction,roundtrip'],
            'flight_number_return'     => ['nullable','string','max:20','required_with:pickup_time_return','required_if:direction,roundtrip','prohibited_unless:direction,roundtrip'],

            'adults'    => ['required','integer','min:1'],
            'children'  => ['nullable','integer','min:0'],
            'infants'   => ['nullable','integer','min:0'],

            'price_total' => ['required','numeric','min:0'],
            'currency'    => ['required','string','size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_location_id.different' => 'Nereye alanı Nereden ile aynı olamaz.',
            'return_date.required_if'  => 'Gidiş–Dönüş seçildiğinde dönüş tarihi zorunludur.',
            'pickup_time_return.required_if' => 'Gidiş–Dönüş seçildiğinde dönüş saati zorunludur.',
        ];
    }
}
