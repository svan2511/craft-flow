<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KarigarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $this->relationLoaded('payments')
            && ($this->relationLoaded('orders') || $this->relationLoaded('workOrders'));
        $orders = $this->workingOrders();
        $counts = $this->jobCounts();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'default_rate' => $this->default_rate !== null ? (float) $this->default_rate : null,
            'phone' => $this->phone,
            'orders_count' => $orders->count(),
            'active_orders' => $counts['active'],
            'completed_orders' => $counts['completed'],
            'pending_orders' => $counts['pending'],
            'ledger' => $this->when($detail, fn () => $this->ledgerSummary()),
            'orders' => $this->when($detail, fn () => $orders->map(fn ($order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'item_name' => $order->item_name,
                'status' => $order->status,
                'status_label' => ucwords(str_replace('_', ' ', $order->status)),
                'created_at' => $order->created_at->toISOString(),
                'current_stage' => $this->currentStage($order),
                'due' => $this->orderDue($order),
                'received' => $this->orderReceived($order),
                'pending' => $this->orderPending($order),
            ])->values()),
            'payments' => $this->when($this->relationLoaded('payments'), fn () => PaymentResource::collection($this->payments)),
        ];
    }

    /**
     * The stage this karigar is currently on within the order: the furthest
     * stage assigned to them in the production sequence, with its live status.
     * Returns null when the karigar has no stages on the order.
     *
     * @return array{name: string, status: string, status_label: string, completed_stages: int, assigned_stages: int}|null
     */
    protected function currentStage(Order $order): ?array
    {
        $stages = $order->relationLoaded('stages')
            ? $order->stages
            : $order->stages()->get();

        $mine = $stages->where('karigar_id', $this->id);

        if ($mine->isEmpty()) {
            return null;
        }

        $furthest = $mine
            ->sortBy(fn ($stage) => array_search($stage->name, OrderStage::STAGE_ORDER, true) === false
                ? 999
                : array_search($stage->name, OrderStage::STAGE_ORDER, true))
            ->last();

        return [
            'name' => $furthest->name,
            'status' => $furthest->status,
            'status_label' => ucwords(str_replace('_', ' ', $furthest->status)),
            'completed_stages' => $mine->where('status', OrderStage::STATUS_COMPLETED)->count(),
            'assigned_stages' => $mine->count(),
        ];
    }
}
