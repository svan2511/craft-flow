<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => ucwords(str_replace('_', ' ', $this->type)),
            'amount' => (float) $this->amount,
            'advance_remaining' => $this->advance_remaining !== null
                ? (float) $this->advance_remaining
                : null,
            'mode' => $this->mode,
            'note' => $this->note,
            'paid_at' => $this->paid_at?->format('Y-m-d'),
            'order_id' => $this->order_id,
            'karigar_id' => $this->karigar_id,
            'stage_id' => $this->stage_id,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_no' => $this->order->order_no,
            ]),
            'stage' => $this->whenLoaded('stage', fn () => [
                'id' => $this->stage->id,
                'name' => $this->stage->name,
            ]),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
