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

            // OUTBOUND: Saat ve uçuş numarası opsiyonel, ama en az bir tanesi dolu olmalı
            'pickup_time_outbound'   => ['nullable','date_format:H:i'],
            'flight_number_outbound' => ['nullable','string','max:20'],

            // RETURN: Sadece roundtrip ise kullanılabilir, en az bir tanesi dolu olmalı
            'pickup_time_return'   => ['nullable','date_format:H:i','prohibited_unless:direction,roundtrip'],
            'flight_number_return' => ['nullable','string','max:20','prohibited_unless:direction,roundtrip'],

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
        ];
    }

    /**
     * Ek kurallar:
     * - Gidiş için (outbound) saat VEYA uçuş numarasından en az biri zorunlu
     * - direction=roundtrip ise dönüş için (return) de saat VEYA uçuş numarasından en az biri zorunlu
     * - direction=oneway ise dönüş alanları zaten prohibited_unless ile kapatılıyor
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $direction = $this->input('direction');

            // OUTBOUND: en az biri dolu olmalı
            $pickupOutbound  = $this->input('pickup_time_outbound');
            $flightOutbound  = $this->input('flight_number_outbound');

            if (empty($pickupOutbound) && empty($flightOutbound)) {
                $validator->errors()->add(
                    'pickup_time_outbound',
                    'Gidiş için saat veya uçuş numarasından en az biri zorunludur.'
                );
            }

            // RETURN: sadece roundtrip’te kontrol et
            if ($direction === 'roundtrip') {
                $pickupReturn = $this->input('pickup_time_return');
                $flightReturn = $this->input('flight_number_return');

                if (empty($pickupReturn) && empty($flightReturn)) {
                    $validator->errors()->add(
                        'pickup_time_return',
                        'Gidiş–Dönüş seçildiğinde dönüş için saat veya uçuş numarasından en az biri zorunludur.'
                    );
                }
            }
        });
    }
}
