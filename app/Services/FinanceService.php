<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Customer;
use App\Models\Karigar;
use App\Models\Order;
use App\Models\OrderStage;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function __construct(
        protected PaymentRepository $payments,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function receivePayment(array $data): ?Payment
    {
        return DB::transaction(function () use ($data) {
            $order = Order::with('customer')->find($data['order_id']);

            if ($order === null) {
                return null;
            }

            $type = $data['type'] ?? Payment::TYPE_ORDER_ADVANCE;
            $amount = (float) $data['amount'];

            if (in_array($type, [
                Payment::TYPE_ORDER_ADVANCE,
                Payment::TYPE_ORDER_MILESTONE,
                Payment::TYPE_ORDER_BALANCE,
            ], true)) {
                $order->increment('advance_paid', $amount);
            }

            $payment = $this->payments->create([
                'workshop_id' => auth()->user()->workshop_id,
                'order_id' => $order->id,
                'type' => $type,
                'amount' => $amount,
                'mode' => $data['mode'] ?? 'cash',
                'note' => $data['note'] ?? null,
                'paid_at' => $data['date'] ?? now()->toDateString(),
            ]);

            if ($order->customer?->phone && $order->balanceDue() <= 0) {
                SendWhatsAppMessageJob::dispatch(
                    $order->customer->phone,
                    "Namaste {$order->customer->name}! Your order {$order->order_no} for '{$order->item_name}' is fully paid. Total paid: Rs {$order->total_amount}. Thank you for your business at {$order->workshop->name}!"
                );
            }

            return $payment;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function reportSummary(): array
    {
        $orderTypes = [Payment::TYPE_ORDER_ADVANCE, Payment::TYPE_ORDER_MILESTONE, Payment::TYPE_ORDER_BALANCE];
        $karigarTypes = [
            Payment::TYPE_KARIGAR_ADVANCE,
            Payment::TYPE_KARIGAR_SETTLEMENT,
            Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
        ];

        $periods = [
            'today' => now()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'this_year' => now()->startOfYear(),
        ];

        $collections = [];
        $karigarOutflow = [];
        $profit = [];

        foreach ($periods as $key => $from) {
            $paid = $this->payments->query()
                ->where('paid_at', '>=', $from->toDateString())
                ->whereIn('type', $orderTypes)
                ->get();

            $collections[$key] = [
                'total' => round((float) $paid->sum('amount'), 2),
                'advance' => round((float) $paid->where('type', Payment::TYPE_ORDER_ADVANCE)->sum('amount'), 2),
                'milestone' => round((float) $paid->where('type', Payment::TYPE_ORDER_MILESTONE)->sum('amount'), 2),
                'balance' => round((float) $paid->where('type', Payment::TYPE_ORDER_BALANCE)->sum('amount'), 2),
                'modes' => [
                    'cash' => round((float) $paid->where('mode', 'cash')->sum('amount'), 2),
                    'upi' => round((float) $paid->where('mode', 'upi')->sum('amount'), 2),
                    'online' => round((float) $paid->where('mode', 'online')->sum('amount'), 2),
                    'cheque' => round((float) $paid->where('mode', 'cheque')->sum('amount'), 2),
                ],
            ];

            $karigarPaid = $this->payments->query()
                ->where('paid_at', '>=', $from->toDateString())
                ->whereIn('type', $karigarTypes)
                ->get();

            $karigarOutflow[$key] = [
                'total' => round((float) $karigarPaid->sum('amount'), 2),
                'advance' => round((float) $karigarPaid->where('type', Payment::TYPE_KARIGAR_ADVANCE)->sum('amount'), 2),
                'settlement' => round((float) $karigarPaid->where('type', Payment::TYPE_KARIGAR_SETTLEMENT)->sum('amount'), 2),
            ];

            $orders = Order::where('created_at', '>=', $from)->with('stages')->get();
            $revenue = round((float) $orders->sum('total_amount'), 2);
            $material = round((float) $orders->sum('material_cost'), 2);

            // Labour only counts once actually paid out to karigars.
            $labor = round((float) $this->payments->query()
                ->where('paid_at', '>=', $from->toDateString())
                ->whereIn('type', $karigarTypes)
                ->sum('amount'), 2);

            $net = round($revenue - $material - $labor, 2);

            $profit[$key] = [
                'revenue' => $revenue,
                'material' => $material,
                'labor' => $labor,
                'net' => $net,
                'margin' => $revenue > 0 ? round(($net / $revenue) * 100, 1) : 0,
            ];
        }

        $byStatus = [];
        foreach (Order::STATUSES as $status) {
            $byStatus[$status] = Order::where('status', $status)->count();
        }

        $stageRows = OrderStage::query()->get();
        $stageFunnel = [];
        foreach (OrderStage::STAGE_ORDER as $name) {
            $rows = $stageRows->where('name', $name);

            $stageFunnel[] = [
                'name' => $name,
                'pending' => $rows->where('status', OrderStage::STATUS_PENDING)->count(),
                'in_progress' => $rows->where('status', OrderStage::STATUS_IN_PROGRESS)->count(),
                'completed' => $rows->where('status', OrderStage::STATUS_COMPLETED)->count(),
                'total' => $rows->count(),
            ];
        }

        $activeOrders = Order::where('status', '!=', Order::STATUS_COMPLETED)->get();
        $customerPending = round((float) $activeOrders
            ->reduce(fn (float $carry, Order $o) => $carry + $o->balanceDue(), 0.0), 2);
        $pendingOrders = $activeOrders->filter(fn (Order $o) => $o->balanceDue() > 0)->count();

        $karigars = Karigar::with(['orders.stages', 'workOrders.stages', 'payments'])->get();
        $karigarPending = round((float) $karigars
            ->reduce(fn (float $carry, Karigar $k) => $carry + $k->ledgerSummary()['total_pending'], 0.0), 2);

        $topCustomers = Customer::with('orders')->get()
            ->map(function (Customer $c) {
                $active = $c->orders->where('status', '!=', Order::STATUS_COMPLETED);

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'pending' => round((float) $active
                        ->reduce(fn (float $carry, Order $o) => $carry + $o->balanceDue(), 0.0), 2),
                    'orders' => $active->count(),
                ];
            })
            ->filter(fn (array $r) => $r['pending'] > 0)
            ->sortByDesc('pending')
            ->take(5)
            ->values()
            ->all();

        $karigarPayouts = $karigars
            ->map(function (Karigar $k) {
                $ledger = $k->ledgerSummary();

                return [
                    'id' => $k->id,
                    'name' => $k->name,
                    'role' => $k->role,
                    'due' => $ledger['total_due'],
                    'paid' => $ledger['total_received'],
                    'pending' => $ledger['total_pending'],
                ];
            })
            ->filter(fn (array $r) => $r['pending'] > 0)
            ->sortByDesc('pending')
            ->take(5)
            ->values()
            ->all();

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthly[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'revenue' => round((float) $this->payments->query()
                    ->whereBetween('paid_at', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()])
                    ->whereIn('type', $orderTypes)
                    ->sum('amount'), 2),
            ];
        }

        return [
            'collections' => $collections,
            'karigar_outflow' => $karigarOutflow,
            'balance_sheet' => [
                'customer_pending' => $customerPending,
                'pending_orders' => $pendingOrders,
                'karigar_pending' => $karigarPending,
                'net' => round($customerPending - $karigarPending, 2),
            ],
            'orders_by_status' => $byStatus,
            'stage_funnel' => $stageFunnel,
            'profit' => $profit,
            'monthly_revenue' => $monthly,
            'top_customers' => $topCustomers,
            'karigar_payouts' => $karigarPayouts,
        ];
    }
}
