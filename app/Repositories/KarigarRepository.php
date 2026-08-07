<?php

namespace App\Repositories;

use App\Models\Karigar;
use Illuminate\Database\Eloquent\Collection;

class KarigarRepository
{
    public function all(): Collection
    {
        return Karigar::query()
            ->with([
                'orders' => fn ($q) => $q->select('id', 'karigar_id', 'status'),
                'orders.stages' => fn ($q) => $q->select('id', 'order_id', 'karigar_id', 'status'),
                'workOrders' => fn ($q) => $q->select('orders.id', 'orders.karigar_id', 'orders.status'),
                'workOrders.stages' => fn ($q) => $q->select('id', 'order_id', 'karigar_id', 'status'),
            ])
            ->latest()
            ->get();
    }

    public function findWithLedger(int $id): ?Karigar
    {
        return Karigar::query()
            ->with([
                'orders.stages',
                'workOrders.stages',
                'payments.order' => fn ($q) => $q->select('id', 'order_no'),
                'payments.stage' => fn ($q) => $q->select('id', 'name'),
            ])
            ->find($id);
    }

    public function create(array $data): Karigar
    {
        return Karigar::create($data);
    }
}
