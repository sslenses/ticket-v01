<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Controller will authorize using policies
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
            'label' => 'required|string|unique:tickets,label',
            'source_device' => 'required|string',
            'destination_device' => 'required|string',
            'source_tenant_id' => 'required|uuid',
            'destination_tenant_id' => 'required|uuid',
            'connector_type' => 'required|string',
            'cable_details' => 'nullable|array',
        ];
    }
}
