<?php

namespace App\Http\Requests\Api\V1\AI;

use Illuminate\Foundation\Http\FormRequest;

class RecommendMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ];
    }
}