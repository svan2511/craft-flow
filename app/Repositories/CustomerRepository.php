<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function all(): Collection
    {
        return Customer::query()
            ->with('orders')
            ->latest()
            ->get();
    }

    public function findByPhone(string $phone): ?Customer
    {
        return Customer::query()
            ->with('orders')
            ->where('phone', $phone)
            ->first();
    }
}
