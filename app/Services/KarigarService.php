<?php

namespace App\Services;

use App\Models\Karigar;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\KarigarRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KarigarService
{
    public function __construct(
        protected KarigarRepository $karigars,
        protected PaymentRepository $payments,
    ) {}

    public function index(): Collection
    {
        return $this->karigars->all();
    }

    public function show(int $id): ?Karigar
    {
        return $this->karigars->findWithLedger($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Karigar
    {
        return $this->karigars->create([
            'name' => $data['name'],
            'role' => $data['role'] ?? null,
            'default_rate' => $data['default_rate'],
            'phone' => $data['phone'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function giveAdvance(Karigar $karigar, array $data): Payment
    {
        return DB::transaction(function () use ($karigar, $data) {
            $orderId = $data['order_id'] ?? null;
            $order = $this->involvedOrder($karigar, $orderId);

            return $this->payments->create([
                'workshop_id' => auth()->user()->workshop_id,
                'karigar_id' => $karigar->id,
                'order_id' => $orderId,
                'stage_id' => $karigar->stageForPayment($order)?->id,
                'type' => Payment::TYPE_KARIGAR_ADVANCE,
                'amount' => $data['amount'],
                'mode' => $data['mode'] ?? 'cash',
                'note' => $data['note'] ?? 'Advance',
                'paid_at' => $data['date'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function settleWeekly(Karigar $karigar, array $data): ?Payment
    {
        return DB::transaction(function () use ($karigar, $data) {
            $orderId = $data['order_id'] ?? null;
            $amount = $data['amount'] ?? null;
            $order = $this->involvedOrder($karigar, $orderId);

            if ($amount === null) {
                if ($orderId !== null) {
                    if ($order === null) {
                        return null;
                    }

                    $amount = $karigar->orderPending($order);
                } else {
                    $weekly = $karigar->weeklyEarnings();
                    $amount = $weekly['net'];
                }
            }

            if ((float) $amount <= 0) {
                return null;
            }

            return $this->payments->create([
                'workshop_id' => auth()->user()->workshop_id,
                'karigar_id' => $karigar->id,
                'order_id' => $orderId,
                'stage_id' => $karigar->stageForPayment($order)?->id,
                'type' => Payment::TYPE_KARIGAR_SETTLEMENT,
                'amount' => $amount,
                'mode' => $data['mode'] ?? 'cash',
                'note' => $data['note'] ?? 'Weekly settlement',
                'paid_at' => $data['date'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * Resolve the order a payment is being recorded against, but only when the
     * karigar is actually involved in it: either as the order lead
     * (orders.karigar_id) or as a production stage worker
     * (order_stages.karigar_id). This mirrors how the ledger builds its order
     * list via workingOrders(), so a stage-assigned karigar's settlement is
     * attributed to the correct stage instead of silently losing it.
     */
    protected function involvedOrder(Karigar $karigar, ?int $orderId): ?Order
    {
        if ($orderId === null) {
            return null;
        }

        $involved = $karigar->workingOrders()->contains('id', $orderId);

        return $involved ? Order::find($orderId) : null;
    }
}
