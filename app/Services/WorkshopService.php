<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workshop;

class WorkshopService
{
    /**
     * @param  array{workshop_name: string, owner_name?: ?string, city?: ?string, phone?: ?string, address?: ?string}  $data
     */
    public function create(User $user, array $data): Workshop
    {
        $workshop = Workshop::create([
            'name' => $data['workshop_name'],
            'owner_name' => $data['owner_name'] ?? null,
            'city' => $data['city'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $user->update(['workshop_id' => $workshop->id]);

        return $workshop;
    }
}
