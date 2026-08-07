<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'karigar_id' => ['required', 'integer', Rule::exists('karigars', 'id')],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(OrderStage::STATUSES)],
        ];
    }

    /**
     * A stage that is already `completed` is final and cannot be changed.
     * Otherwise a stage cannot be moved to `in_progress` or `completed`
     * until the canonical stage before it is completed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $order = Order::query()->find($this->route('order'));

                if ($order === null) {
                    return;
                }

                $stage = $order->stages()->find($this->route('stage'));

                if ($stage === null) {
                    return;
                }

                if ($stage->status === OrderStage::STATUS_COMPLETED) {
                    $validator->errors()->add(
                        'status',
                        "Stage \"{$stage->name}\" already completed; it cannot be changed."
                    );

                    return;
                }

                $status = $this->input('status');

                if (! in_array($status, [OrderStage::STATUS_IN_PROGRESS, OrderStage::STATUS_COMPLETED], true)) {
                    return;
                }

                $previous = OrderStage::previousStageName($stage->name);

                if ($previous === null) {
                    return;
                }

                $prevStage = $order->stages()->where('name', $previous)->first();

                if ($prevStage === null || $prevStage->status !== OrderStage::STATUS_COMPLETED) {
                    $validator->errors()->add(
                        'status',
                        "Pahle \"{$previous}\" complete karein, tabhi \"{$stage->name}\" shuru/complete ho sakti hai."
                    );
                }
            },
        ];
    }
}
