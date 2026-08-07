<?php

namespace App\Models;

use App\Models\Scopes\WorkshopScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karigar extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'name',
        'role',
        'default_rate',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'default_rate' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkshopScope);

        static::creating(function (Karigar $karigar) {
            if ($karigar->workshop_id === null) {
                $karigar->workshop_id = auth()->user()?->workshop_id;
            }
        });
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Orders this karigar is assigned to as a production stage worker
     * (order_stages.karigar_id). The plain `orders` relation only covers the
     * order "lead" (orders.karigar_id), so stage-assigned workers like a
     * second-stage karigar would otherwise appear to have no work at all.
     */
    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_stages', 'karigar_id', 'order_id');
    }

    /**
     * Every order this karigar is actually involved in: lead orders plus any
     * order they hold a production stage on, deduplicated by order id.
     */
    public function workingOrders(): \Illuminate\Database\Eloquent\Collection
    {
        $lead = $this->relationLoaded('orders')
            ? $this->orders
            : $this->orders()->get();

        $staged = $this->relationLoaded('workOrders')
            ? $this->workOrders
            : $this->workOrders()->get();

        return $lead->merge($staged)->unique('id')->values();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Amount earned (due) by this karigar on a single order, i.e. the labor of
     * the COMPLETED stages assigned to them. Falls back to the order-level
     * `worker_labor_cost` when the order has no stage-wise tracking.
     */
    public function orderDue(Order $order): float
    {
        $stages = $order->relationLoaded('stages')
            ? $order->stages
            : $order->stages()->get();

        $mine = $stages->where('karigar_id', $this->id);

        if ($mine->isNotEmpty()) {
            return round((float) $mine
                ->where('status', OrderStage::STATUS_COMPLETED)
                ->sum('labor_cost'), 2);
        }

        return round((float) $order->worker_labor_cost, 2);
    }

    /**
     * Money already received by this karigar against a single order
     * (settlements + advances), i.e. what has actually been paid out.
     */
    public function orderReceived(Order $order): float
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        return round((float) $payments
            ->where('order_id', $order->id)
            ->whereIn('type', [Payment::TYPE_KARIGAR_SETTLEMENT, Payment::TYPE_KARIGAR_ADVANCE])
            ->sum('amount'), 2);
    }

    /**
     * Outstanding amount still owed to the karigar on a single order.
     */
    public function orderPending(Order $order): float
    {
        return round($this->orderDue($order) - $this->orderReceived($order), 2);
    }

    /**
     * The stage this payment should be attributed to: the furthest COMPLETED
     * stage assigned to this karigar on the order (the work being paid for).
     * Falls back to the furthest assigned stage when none is completed yet,
     * and to null when the karigar has no stages on the order.
     */
    public function stageForPayment(?Order $order): ?OrderStage
    {
        if ($order === null) {
            return null;
        }

        $stages = $order->relationLoaded('stages')
            ? $order->stages
            : $order->stages()->get();

        $mine = $stages->where('karigar_id', $this->id);

        if ($mine->isEmpty()) {
            return null;
        }

        $bySequence = fn (OrderStage $stage) => array_search($stage->name, OrderStage::STAGE_ORDER, true) === false
            ? 999
            : array_search($stage->name, OrderStage::STAGE_ORDER, true);

        $completed = $mine->where('status', OrderStage::STATUS_COMPLETED)->sortBy($bySequence);

        if ($completed->isNotEmpty()) {
            return $completed->last();
        }

        return $mine->sortBy($bySequence)->last();
    }

    /**
     * Job stats for this karigar, counted from the stages assigned to them
     * (not the order-level status). Legacy orders without any stage rows fall
     * back to the order status.
     *
     * @return array{active: int, completed: int, pending: int}
     */
    public function jobCounts(): array
    {
        $orders = $this->workingOrders();

        $active = 0;
        $completed = 0;
        $pending = 0;

        foreach ($orders as $order) {
            $stages = $order->relationLoaded('stages')
                ? $order->stages
                : $order->stages()->get();

            $mine = $stages->where('karigar_id', $this->id);

            if ($mine->isNotEmpty()) {
                $completed += $mine->where('status', OrderStage::STATUS_COMPLETED)->count();
                $active += $mine->where('status', OrderStage::STATUS_IN_PROGRESS)->count();
                $pending += $mine->where('status', OrderStage::STATUS_PENDING)->count();
                continue;
            }

            if ($order->status === Order::STATUS_COMPLETED) {
                $completed++;
            } elseif (in_array($order->status, [
                Order::STATUS_IN_STRUCTURE,
                Order::STATUS_IN_POLISH,
                Order::STATUS_READY,
            ], true)) {
                $active++;
            } else {
                $pending++;
            }
        }

        return ['active' => $active, 'completed' => $completed, 'pending' => $pending];
    }

    /**
     * Weekly statement (Mon-Sun): money earned by completed work (`due`),
     * money actually paid out (`received`/`paid`), and what is still pending.
     *
     * @return array<string, float>
     */
    public function weeklyEarnings(): array
    {
        $start = now()->startOfWeek();

        $orders = $this->workingOrders();

        $due = round((float) $orders
            ->filter(fn (Order $order) => $order->created_at >= $start)
            ->reduce(fn (float $carry, Order $order) => $carry + $this->orderDue($order), 0.0), 2);

        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        $advances = round((float) $payments
            ->where('type', Payment::TYPE_KARIGAR_ADVANCE)
            ->where('paid_at', '>=', $start->toDateString())
            ->sum('amount'), 2);

        $settled = round((float) $payments
            ->where('type', Payment::TYPE_KARIGAR_SETTLEMENT)
            ->where('paid_at', '>=', $start->toDateString())
            ->sum('amount'), 2);

        $paid = round($advances + $settled, 2);

        return [
            'due' => $due,
            'received' => $paid,
            'paid' => $paid,
            'advances' => $advances,
            'settled' => $settled,
            'pending' => round($due - $paid, 2),
            'net' => round($due - $paid, 2),
        ];
    }

    /**
     * Full ledger: money earned from completed work (`total_due`), money paid
     * out (`total_received`), and what is still pending to be given.
     *
     * @return array<string, mixed>
     */
    public function ledgerSummary(): array
    {
        $orders = $this->workingOrders();

        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        $due = round((float) $orders
            ->reduce(fn (float $carry, Order $order) => $carry + $this->orderDue($order), 0.0), 2);

        $advances = round((float) $payments
            ->where('type', Payment::TYPE_KARIGAR_ADVANCE)
            ->sum('amount'), 2);

        $settled = round((float) $payments
            ->where('type', Payment::TYPE_KARIGAR_SETTLEMENT)
            ->sum('amount'), 2);

        $received = round($advances + $settled, 2);

        return [
            'total_due' => $due,
            'total_received' => $received,
            'total_pending' => round($due - $received, 2),
            'total_settled' => $settled,
            'total_advances' => $advances,
            'balance' => round($due - $received, 2),
        ];
    }
}
