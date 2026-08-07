<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function getWorkshopOrders(?string $status = null): Collection
    {
        return Order::query()
            ->with(['customer', 'karigar', 'stages'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();
    }

    public function findForWorkshop(int $id): ?Order
    {
        return Order::query()
            ->with(['customer', 'karigar', 'payments', 'stages.karigar'])
            ->find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function updateStatus(Order $order, string $status, ?float $laborCost = null): Order
    {
        $data = ['status' => $status];
        if ($laborCost !== null) {
            $data['worker_labor_cost'] = $laborCost;
        }
        $order->update($data);

        return $order;
    }

    public function upsertCustomer(int $workshopId, string $name, string $phone): Customer
    {
        $customer = Customer::query()
            ->where('phone', $phone)
            ->first();

        if ($customer !== null) {
            if ($customer->name !== $name) {
                $customer->update(['name' => $name]);
            }

            return $customer;
        }

        return Customer::create([
            'workshop_id' => $workshopId,
            'name' => $name,
            'phone' => $phone,
        ]);
    }

    public function incrementCustomerOrders(Customer $customer): void
    {
        $customer->increment('total_orders');
    }
}
