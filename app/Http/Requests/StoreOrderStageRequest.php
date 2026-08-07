<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'karigar_id' => ['required', 'integer', Rule::exists('karigars', 'id')],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(OrderStage::STATUSES)],
        ];
    }

    /**
     * Enforce the canonical production order: a stage can only be added once
     * the one before it is already assigned to the order.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $order = Order::query()->find($this->route('order'));

                if ($order === null) {
                    return;
                }

                $next = OrderStage::nextStageFor($order);

                if ($next === null) {
                    $validator->errors()->add('name', 'Saari production stages is order mein add ho chuki hain.');

                    return;
                }

                if ($this->input('name') !== $next) {
                    $validator->errors()->add('name', "Stage order ke hisaab se agli stage \"{$next}\" add karein.");

                    return;
                }

                $previous = OrderStage::previousStageName($next);

                if ($previous !== null) {
                    $prevStage = $order->stages()->where('name', $previous)->first();

                    if ($prevStage === null || $prevStage->status !== OrderStage::STATUS_COMPLETED) {
                        $validator->errors()->add(
                            'name',
                            "Pahle \"{$previous}\" complete karein, tabhi \"{$next}\" assign ho sakti hai."
                        );
                    }
                }
            },
        ];
    }
}
