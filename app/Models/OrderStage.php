<?php

namespace App\Models;

use App\Models\Scopes\WorkshopScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStage extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
    ];

    /**
     * The canonical production stages for a furniture order, in the order they
     * must be completed. Work can only be assigned to the next stage in this
     * sequence; skipping ahead is not allowed.
     *
     * @var string[]
     */
    public const STAGE_ORDER = [
        'Structure/Cutting',
        'Carving',
        'Assembly',
        'Sanding/Polishing',
        'Fitting',
        'Packaging',
    ];

    /**
     * The next stage that can be added to an order, i.e. the first canonical
     * stage not yet present. Returns null once every stage is assigned.
     */
    public static function nextStageFor(Order $order): ?string
    {
        $names = $order->stages()->pluck('name');

        foreach (self::STAGE_ORDER as $stage) {
            if (! $names->contains($stage)) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * The canonical stage that must be completed before the given stage can be
     * started or assigned. Returns null for the first stage (nothing precedes
     * it) or for names outside the canonical order.
     */
    public static function previousStageName(string $name): ?string
    {
        $index = array_search($name, self::STAGE_ORDER, true);

        if ($index === false || $index === 0) {
            return null;
        }

        return self::STAGE_ORDER[$index - 1];
    }

    protected $fillable = [
        'workshop_id',
        'order_id',
        'karigar_id',
        'name',
        'labor_cost',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'labor_cost' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkshopScope);

        static::creating(function (OrderStage $stage) {
            if ($stage->workshop_id === null && $stage->order_id !== null) {
                $stage->workshop_id = $stage->order?->workshop_id;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function karigar(): BelongsTo
    {
        return $this->belongsTo(Karigar::class);
    }
}
