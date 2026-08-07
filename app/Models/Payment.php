<?php

namespace App\Models;

use App\Models\Scopes\WorkshopScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const TYPE_ORDER_ADVANCE = 'order_advance';

    public const TYPE_ORDER_MILESTONE = 'order_milestone';

    public const TYPE_ORDER_BALANCE = 'order_balance';

    public const TYPE_KARIGAR_ADVANCE = 'karigar_advance';

    public const TYPE_KARIGAR_SETTLEMENT = 'karigar_settlement';

    public const TYPE_KARIGAR_STAGE_SETTLEMENT = 'karigar_stage_settlement';

    public const MODES = ['cash', 'online', 'upi', 'cheque'];

    protected $fillable = [
        'workshop_id',
        'order_id',
        'karigar_id',
        'stage_id',
        'type',
        'amount',
        'advance_remaining',
        'mode',
        'note',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'advance_remaining' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkshopScope);

        static::creating(function (Payment $payment) {
            if ($payment->workshop_id === null) {
                $payment->workshop_id = auth()->user()?->workshop_id;
            }
        });
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function karigar(): BelongsTo
    {
        return $this->belongsTo(Karigar::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(OrderStage::class);
    }
}
