<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'mode' => ['nullable', Rule::in(['cash', 'online', 'upi', 'cheque'])],
            'note' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'type' => ['nullable', Rule::in(['order_advance', 'order_milestone', 'order_balance'])],
        ];
    }
}
