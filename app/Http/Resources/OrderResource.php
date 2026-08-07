<?php

namespace App\Http\Resources;

use App\Models\OrderStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $current = $this->whenLoaded('stages', function () {
            $stages = $this->stages
                ->sortBy(fn ($stage) => array_search($stage->name, OrderStage::STAGE_ORDER, true) ?? 999);

            $inProgress = $stages->first(fn ($stage) => $stage->status === OrderStage::STATUS_IN_PROGRESS);
            if ($inProgress !== null) {
                return $inProgress;
            }

            $lastCompleted = $stages->last(fn ($stage) => $stage->status === OrderStage::STATUS_COMPLETED);
            if ($lastCompleted !== null) {
                return $lastCompleted;
            }

            return null;
        });

        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'item_name' => $this->item_name,
            'status' => $this->status,
            'progress' => $this->progress,
            'total_amount' => (float) $this->total_amount,
            'advance_paid' => (float) $this->advance_paid,
            'balance_due' => $this->balanceDue(),
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'created_at' => $this->created_at->toISOString(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ]),
            'karigar' => $this->whenLoaded('karigar', fn () => [
                'id' => $this->karigar->id,
                'name' => $this->karigar->name,
                'role' => $this->karigar->role,
            ]),
            'current_stage' => $current !== null ? [
                'name' => $current->name,
                'status' => $current->status,
            ] : null,
        ];
    }
}
