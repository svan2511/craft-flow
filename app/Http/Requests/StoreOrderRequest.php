<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'digits:10'],
            'item_name' => ['required', 'string', 'max:150'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'advance_paid' => ['nullable', 'numeric', 'min:0'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'karigar_id' => ['nullable', 'integer', Rule::exists('karigars', 'id')],
            'worker_labor_cost' => ['nullable', 'numeric', 'min:0'],
            'material_cost' => ['nullable', 'numeric', 'min:0'],
            'customization_notes' => ['nullable', 'string'],
            'design_image' => ['nullable', 'string', 'max:5242880'],
            'design_images' => ['nullable', 'array', 'max:3'],
            'design_images.*' => ['string', 'max:5242880'],            'send_whatsapp' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone number is required.',
            'item_name.required' => 'Item name is required.',
            'total_amount.required' => 'Total amount is required.',
        ];
    }
}
