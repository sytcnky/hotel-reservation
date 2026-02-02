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

            // Client price/currency göndermez; server-side hesaplanır
            'currency'    => ['prohibited'],
            'price_total' => ['prohibited'],

            // S0-1: client-sourced display/name/label kesinlikle kabul edilmez
            'from_label'   => ['prohibited'],
            'to_label'     => ['prohibited'],
            'vehicle_name' => ['prohibited'],
        ];
    }

    /**
     * Validation mesajları string değil, ui-code döner.
     * UI katmanı bu kodu t() ile çözer.
     */
    public function messages(): array
    {
        return [
            'to_location_id.different' => 'validation.transfer.to_location_id_different',

            'return_date.required'     => 'validation.transfer.return_date_required',
            'return_date.prohibited'   => 'validation.transfer.return_date_prohibited',

            // Bu iki kural “composite/global” kabul edildiği için
            // render_target=global olacak şekilde withValidator’da 'global' key'ine taşınacak.
            'pickup_time_return.prohibited_unless'   => 'validation.transfer.return_fields_prohibited',
            'flight_number_return.prohibited_unless' => 'validation.transfer.return_fields_prohibited',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $direction = (string) $this->input('direction');

            $pickupOutbound = $this->input('pickup_time_outbound');
            $flightOutbound = $this->input('flight_number_outbound');

            // Gidiş için saat veya uçuş no en az biri zorunlu (global)
            if (empty($pickupOutbound) && empty($flightOutbound)) {
                $validator->errors()->add(
                    'global',
                    'validation.transfer.pickup_time_outbound_or_flight_required'
                );
            }

            if ($direction === 'roundtrip') {
                $pickupReturn = $this->input('pickup_time_return');
                $flightReturn = $this->input('flight_number_return');

                // Dönüş için saat veya uçuş no en az biri zorunlu (global)
                if (empty($pickupReturn) && empty($flightReturn)) {
                    $validator->errors()->add(
                        'global',
                        'validation.transfer.pickup_time_return_or_flight_required'
                    );
                }
            }

            // prohibited_unless kaynaklı field error’larını “global”e yansıt (render_target=global)
            if ($validator->errors()->has('pickup_time_return') || $validator->errors()->has('flight_number_return')) {
                $msg = 'validation.transfer.return_fields_prohibited';

                if (! $validator->errors()->has('global')) {
                    $validator->errors()->add('global', $msg);
                } else {
                    $validator->errors()->add('global', $msg);
                }
            }
        });
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'departure_date' => $this->normalizeDateToYmd($this->input('departure_date')),
            'return_date'    => $this->normalizeDateToYmd($this->input('return_date')),
        ]);
    }
}
