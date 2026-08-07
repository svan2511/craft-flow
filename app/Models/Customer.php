<?php

namespace App\Models;

use App\Models\Scopes\WorkshopScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'name',
        'phone',
        'total_orders',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkshopScope);

        static::creating(function (Customer $customer) {
            if ($customer->workshop_id === null) {
                $customer->workshop_id = auth()->user()?->workshop_id;
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
}
