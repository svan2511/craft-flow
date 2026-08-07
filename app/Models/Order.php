<?php

namespace App\Models;

use App\Models\Scopes\WorkshopScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_STRUCTURE = 'in_structure';

    public const STATUS_IN_POLISH = 'in_polish';

    public const STATUS_READY = 'ready';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_STRUCTURE,
        self::STATUS_IN_POLISH,
        self::STATUS_READY,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'workshop_id',
        'order_no',
        'customer_id',
        'karigar_id',
        'item_name',
        'total_amount',
        'advance_paid',
        'worker_labor_cost',
        'material_cost',
        'delivery_date',
        'status',
        'customization_notes',
        'design_image',
        'design_images',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'advance_paid' => 'decimal:2',
            'worker_labor_cost' => 'decimal:2',
            'material_cost' => 'decimal:2',
            'delivery_date' => 'date',
            'design_images' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkshopScope);

        static::creating(function (Order $order) {
            if ($order->workshop_id === null) {
                $order->workshop_id = auth()->user()?->workshop_id;
            }
        });
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function karigar(): BelongsTo
    {
        return $this->belongsTo(Karigar::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(OrderStage::class);
    }

    public function balanceDue(): float
    {
        return (float) bcsub(
            number_format((float) $this->total_amount, 2, '.', ''),
            number_format((float) $this->advance_paid, 2, '.', ''),
            2
        );
    }

    /**
     * Total labour committed on this order. When stage-wise work is tracked,
     * this is the sum of ALL stage labour costs (completed or not). Otherwise
     * it falls back to the legacy single `worker_labor_cost` value.
     */
    public function laborTotal(): float
    {
        $stages = $this->relationLoaded('stages')
            ? $this->stages
            : $this->stages()->get();

        if ($stages->isNotEmpty()) {
            return round((float) $stages->sum('labor_cost'), 2);
        }

        return (float) $this->worker_labor_cost;
    }

    /**
     * Labour actually earned, i.e. the sum of COMPLETED stages only. Falls
     * back to the legacy `worker_labor_cost` when no stages are tracked.
     */
    public function completedLabor(): float
    {
        $stages = $this->relationLoaded('stages')
            ? $this->stages
            : $this->stages()->get();

        if ($stages->isNotEmpty()) {
            return round((float) $stages
                ->where('status', OrderStage::STATUS_COMPLETED)
                ->sum('labor_cost'), 2);
        }

        return (float) $this->worker_labor_cost;
    }

    public function netProfit(): float
    {
        $cost = (float) $this->material_cost + $this->laborTotal();

        return round((float) $this->total_amount - $cost, 2);
    }

    protected function progress(): Attribute
    {
        return Attribute::make(
            get: fn (): int => match ($this->status) {
                self::STATUS_NEW => 20,
                self::STATUS_IN_STRUCTURE => 45,
                self::STATUS_IN_POLISH => 70,
                self::STATUS_READY => 90,
                self::STATUS_COMPLETED => 100,
                default => 0,
            }
        );
    }
}
