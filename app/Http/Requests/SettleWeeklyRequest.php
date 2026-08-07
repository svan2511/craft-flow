<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettleWeeklyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'amount' => ['nullable', 'numeric', 'min:1'],
            'mode' => ['nullable', 'string', 'max:20'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
