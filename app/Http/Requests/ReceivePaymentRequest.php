<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $order = Order::find($this->input('order_id'));
            if ($order === null) {
                return;
            }

            $amount = (float) $this->input('amount');
            $due = $order->balanceDue();

            if ($amount > $due) {
                $validator->errors()->add('amount', "Payment cannot exceed the remaining due of ₹{$due} on this order.");
            }

            $type = $this->input('type');

            if ($type === Payment::TYPE_ORDER_ADVANCE && $order->payments()->where('type', Payment::TYPE_ORDER_ADVANCE)->exists()) {
                $validator->errors()->add('type', 'This order already has an advance payment recorded. Use Milestone or Balance.');
            }
        });
    }
}
