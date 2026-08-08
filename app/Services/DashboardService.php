<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected PaymentRepository $payments,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $workshopId = auth()->user()->workshop_id;

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        $revenueByType = fn (string $from) => (float) $this->payments->query()
            ->where('paid_at', '>=', $from)
            ->whereIn('type', [Payment::TYPE_ORDER_ADVANCE, Payment::TYPE_ORDER_MILESTONE, Payment::TYPE_ORDER_BALANCE])
            ->sum('amount');

        $profitSince = function (string $from) {
            $orders = Order::where('created_at', '>=', $from)->with('stages')->get();

            $material = round((float) $orders->sum('material_cost'), 2);
            $gross = round((float) $orders->sum('total_amount'), 2);

            // Labour only counts once it is actually paid out to karigars
            // (advance / settlement / stage settlement). Assigning a stage
            // does not hit the profit figure anymore.
            $labor = round((float) $this->payments->query()
                ->where('paid_at', '>=', $from)
                ->whereIn('type', [
                    Payment::TYPE_KARIGAR_ADVANCE,
                    Payment::TYPE_KARIGAR_SETTLEMENT,
                    Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
                ])
                ->sum('amount'), 2);

            return [
                'material' => $material,
                'labor' => $labor,
                'gross' => $gross,
                'net' => round($gross - $material - $labor, 2),
            ];
        };

        $new = Order::where('status', Order::STATUS_NEW)->count();
        $active = Order::whereIn('status', [Order::STATUS_IN_STRUCTURE, Order::STATUS_IN_POLISH])->count();
        $completed = Order::where('status', Order::STATUS_READY)->count();
        $delivered = Order::where('status', Order::STATUS_COMPLETED)->count();

        $outstanding = (float) Order::where('status', '!=', Order::STATUS_COMPLETED)
            ->get()
            ->reduce(fn (float $carry, Order $o) => $carry + $o->balanceDue(), 0.0);

        // Orders whose production is finished are always shown first — they need
        // to be delivered / money collected, regardless of how far out the date is.
        $ready = Order::where('status', Order::STATUS_READY)
            ->with('customer')
            ->withExists(['payments as has_advance' => fn ($q) => $q->where('type', Payment::TYPE_ORDER_ADVANCE)])
            ->orderByRaw('delivery_date IS NULL, delivery_date ASC')
            ->get();

        // Orders still in progress only surface when the delivery date is close.
        $upcoming = Order::whereNotIn('status', [Order::STATUS_READY, Order::STATUS_COMPLETED])
            ->whereNotNull('delivery_date')
            ->where('delivery_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('delivery_date')
            ->with('customer')
            ->withExists(['payments as has_advance' => fn ($q) => $q->where('type', Payment::TYPE_ORDER_ADVANCE)])
            ->get();

        $urgent = $ready->concat($upcoming)->take(8);

        $recent = Order::with('customer')
            ->withExists(['payments as has_advance' => fn ($q) => $q->where('type', Payment::TYPE_ORDER_ADVANCE)])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'workshop' => auth()->user()->workshop,
            'metrics' => [
                'new_orders' => $new,
                'active_orders' => $active,
                'completed_orders' => $completed,
                'delivered_orders' => $delivered,
                'total_orders' => $new + $active + $completed + $delivered,
                'outstanding_balance' => $outstanding,
                'karigars' => auth()->user()->workshop->karigars()->count(),
                'customers' => auth()->user()->workshop->customers()->count(),
            ],
            'revenue' => [
                'today' => $revenueByType($today),
                'this_week' => $revenueByType($weekStart),
                'this_month' => $revenueByType($monthStart),
            ],
            'profit' => [
                'today' => $profitSince($today),
                'this_week' => $profitSince($weekStart),
                'this_month' => $profitSince($monthStart),
            ],
            'urgent_deliveries' => $this->formatOrders($urgent),
            'recent_orders' => $this->formatOrders($recent),
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    protected function formatOrders(Collection $orders): array
    {
        return $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'item_name' => $order->item_name,
            'status' => $order->status,
            'total_amount' => (float) $order->total_amount,
            'balance_due' => $order->balanceDue(),
            'has_advance' => (bool) $order->has_advance,
            'delivery_date' => $order->delivery_date?->format('Y-m-d'),
            'customer' => $order->relationLoaded('customer') && $order->customer
                ? ['id' => $order->customer->id, 'name' => $order->customer->name]
                : null,
        ])->values()->all();
    }
}
