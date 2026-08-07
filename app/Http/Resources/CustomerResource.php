<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $orders = $this->relationLoaded('orders')
            ? $this->orders
            : collect();

        $lastOrder = $orders->sortByDesc('created_at')->first();

        $outstanding = $orders
            ->where('status', '!=', Order::STATUS_COMPLETED)
            ->reduce(fn (float $carry, Order $o) => $carry + $o->balanceDue(), 0.0);

        $completed = $orders
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        $unpaid = $orders
            ->filter(fn (Order $o) => $o->balanceDue() > 0)
            ->count();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'total_orders' => $this->total_orders,
            'completed_orders' => $completed,
            'orders_with_pending_balance' => $unpaid,
            'outstanding_balance' => round($outstanding, 2),
            'last_order' => $lastOrder?->item_name,
            'last_order_date' => $lastOrder?->created_at?->format('Y-m-d'),
        ];
    }
}
