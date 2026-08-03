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
            'source_device' => 'nullable|string',
            'destination_device' => 'nullable|string',
            'source_tenant_id' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== 'NEW_TENANT' && !\Illuminate\Support\Str::isUuid($value)) {
                        $fail('The selected ' . str_replace('_', ' ', $attribute) . ' is invalid.');
                    }
                }
            ],
            'new_source_tenant_name' => 'required_if:source_tenant_id,NEW_TENANT|nullable|string|max:255',
            'destination_tenant_id' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== 'NEW_TENANT' && !\Illuminate\Support\Str::isUuid($value)) {
                        $fail('The selected ' . str_replace('_', ' ', $attribute) . ' is invalid.');
                    }
                }
            ],
            'new_destination_tenant_name' => 'required_if:destination_tenant_id,NEW_TENANT|nullable|string|max:255',
            'connector_type' => 'nullable|string',
            'cable_details' => 'required|array',
            'cable_details.user_name' => 'required|string',
            'cable_details.user_contact' => 'nullable|string',
            'cable_details.backhaul' => 'nullable|string',
            'cable_details.metro' => 'nullable|string',
            'cable_details.destination_site' => 'nullable|string',
            'cable_details.capacity' => 'nullable|string',
            'cable_details.length' => 'nullable|integer',
            'cable_details.color' => 'nullable|string',
            'cable_details.type' => 'nullable|string',
            'cable_details.notes' => 'nullable|string',
            'cable_details.keterangan' => 'nullable|string',
            'cable_details.alamat' => 'nullable|string',
            'cable_details.titik_koordinat' => 'nullable|string',
            'cable_details.link_maps' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'cable_details.user_name' => 'User Name',
        ];
    }
}
