<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'          => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:20'],
            'city'           => ['required', 'string', 'max:100'],
            'area'           => ['required', 'string', 'max:100'],
            'street'         => ['required', 'string', 'max:150'],
            'building'       => ['nullable', 'string', 'max:50'],
            'floor'          => ['nullable', 'string', 'max:20'],
            'apartment'      => ['nullable', 'string', 'max:20'],
            'landmark'       => ['nullable', 'string', 'max:150'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'is_default'     => ['sometimes', 'boolean'],
        ];
    }
}
