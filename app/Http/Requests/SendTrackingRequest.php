<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipment_id' => 'required|string',
            'carrier_id' => 'required|string',
            'stop_sequence' => 'required|integer',
            'tracking_code' => 'required|integer',
            'message_type' => 'required|string',
            'message_name' => 'string'
        ];
    }
}
