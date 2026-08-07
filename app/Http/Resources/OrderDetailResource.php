<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paymentsCol = $this->relationLoaded('payments') ? $this->payments : collect();

        $payments = $paymentsCol->count() > 0
            ? PaymentResource::collection($paymentsCol)
            : collect();

        $received = round((float) collect($paymentsCol)->sum('amount'), 2);

        // Labour only counts once it has actually been paid out to karigars.
        $laborPaid = round((float) collect($paymentsCol)
            ->whereIn('type', [
                Payment::TYPE_KARIGAR_ADVANCE,
                Payment::TYPE_KARIGAR_SETTLEMENT,
                Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
            ])
            ->sum('amount'), 2);

        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'item_name' => $this->item_name,
            'status' => $this->status,
            'progress' => $this->progress,
            'status_label' => ucwords(str_replace('_', ' ', $this->status)),
            'total_amount' => (float) $this->total_amount,
            'advance_paid' => (float) $this->advance_paid,
            'balance_due' => $this->balanceDue(),
            'delivery_date' => $this->delivery_date?->format('Y-m-d'),
            'customization_notes' => $this->customization_notes,
            'design_image' => $this->design_image,
            'design_images' => $this->design_images ?? [],
            'worker_labor_cost' => $this->worker_labor_cost !== null ? (float) $this->worker_labor_cost : null,
            'material_cost' => $this->material_cost !== null ? (float) $this->material_cost : null,
            'labor_cost' => $this->laborTotal(),
            'labor_paid' => $laborPaid,
            'stages' => $this->whenLoaded('stages', fn () => $this->stages->map(fn ($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'status' => $stage->status,
                'status_label' => ucwords(str_replace('_', ' ', $stage->status)),
                'labor_cost' => (float) $stage->labor_cost,
                'completed_at' => $stage->completed_at?->toISOString(),
                'karigar' => $stage->karigar !== null ? [
                    'id' => $stage->karigar->id,
                    'name' => $stage->karigar->name,
                    'role' => $stage->karigar->role,
                ] : null,
            ])->values()),
            'net_profit' => round(
                (float) $this->total_amount - (float) $this->material_cost - $laborPaid,
                2
            ),
            'created_at' => $this->created_at->toISOString(),
            'workshop' => [
                'name' => $this->workshop?->name,
                'phone' => $this->workshop?->phone,
                'address' => $this->workshop?->address,
                'city' => $this->workshop?->city,
            ],
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
            'payments' => $payments,
            'amount_received' => $received,
        ];
    }
}
