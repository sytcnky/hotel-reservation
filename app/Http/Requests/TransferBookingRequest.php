<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesBookingSnapshot;
use Illuminate\Foundation\Http\FormRequest;

class TransferBookingRequest extends FormRequest
{
    use NormalizesBookingSnapshot;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $direction = (string) ($this->input('direction') ?? '');

        $returnDateRules = $direction === 'roundtrip'
            ? ['required', 'date_format:Y-m-d']
            : ['prohibited'];

        return [
            'route_id'         => ['required', 'integer', 'min:1'],
            'vehicle_id'       => ['required', 'integer', 'min:1'],
            'direction'        => ['required', 'in:oneway,roundtrip'],
            'from_location_id' => ['required', 'integer', 'min:1', 'different:to_location_id'],
            'to_location_id'   => ['required', 'integer', 'min:1', 'different:from_location_id'],

            // strict: Y-m-d
            'departure_date'   => ['required', 'date_format:Y-m-d'],
            'return_date'      => $returnDateRules,

            'pickup_time_outbound'   => ['nullable', 'date_format:H:i'],
            'flight_number_outbound' => ['nullable', 'string', 'max:20'],

            'pickup_time_return'   => ['nullable', 'date_format:H:i', 'prohibited_unless:direction,roundtrip'],
            'flight_number_return' => ['nullable', 'string', 'max:20', 'prohibited_unless:direction,roundtrip'],

            'adults'   => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'infants'  => ['nullable', 'integer', 'min:0'],

            'price_total' => ['required', 'numeric', 'min:0'],
            'currency'    => ['required', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_location_id.different' => 'Nereye alanı Nereden ile aynı olamaz.',
            'return_date.required'     => 'Gidiş–Dönüş seçildiğinde dönüş tarihi zorunludur.',
            'return_date.prohibited'   => 'Tek yön seçildiğinde dönüş tarihi gönderilemez.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $direction = $this->input('direction');

            $pickupOutbound = $this->input('pickup_time_outbound');
            $flightOutbound = $this->input('flight_number_outbound');

            if (empty($pickupOutbound) && empty($flightOutbound)) {
                $validator->errors()->add(
                    'pickup_time_outbound',
                    'Gidiş için saat veya uçuş numarasından en az biri zorunludur.'
                );
            }

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

    protected function passedValidation(): void
    {
        $this->merge([
            'departure_date' => $this->normalizeDateToYmd($this->input('departure_date')),
            'return_date'    => $this->normalizeDateToYmd($this->input('return_date')),
            'currency'       => $this->normalizeCurrency($this->input('currency')),
        ]);
    }
}
