<?php

namespace App\Http\Requests;

use App\Models\Fee;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class VehiclePostRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'price' => 'required|numeric|max:99999999',
            'type' => 'required|in:' .  implode(',', Vehicle::TYPES),
            'soldFor' => 'required|numeric|max:99999999',
            'fees' => 'required|array',
            'fees.*.type' => 'required|in:' .  implode(',', Fee::TYPES),
            'fees.*.amount' => 'required|numeric|max:99999999'
        ];
    }
}
