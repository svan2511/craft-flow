<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Karigar;
use App\Models\Order;
use App\Models\OrderStage;
use App\Models\Payment;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderRepository $orders,
        protected PaymentRepository $payments,
    ) {}

    public function index(?string $status = null): Collection
    {
        return $this->orders->getWorkshopOrders($status);
    }

    public function show(int $id): ?Order
    {
        return $this->orders->findForWorkshop($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data, bool $sendWhatsapp = false): Order
    {
        return DB::transaction(function () use ($data, $sendWhatsapp) {
            $workshopId = auth()->user()->workshop_id;
            $customer = $this->orders->upsertCustomer(
                $workshopId,
                $data['customer_name'],
                $data['customer_phone'],
            );
            $imageUrls = $this->uploadDesignImages($data);

            $order = $this->orders->create([
                'workshop_id' => $workshopId,
                'order_no' => $this->generateOrderNo(),
                'customer_id' => $customer->id,
                'karigar_id' => $data['karigar_id'] ?? null,
                'item_name' => $data['item_name'],
                'total_amount' => $data['total_amount'],
                'advance_paid' => $data['advance_paid'] ?? 0,
                'worker_labor_cost' => $data['worker_labor_cost'] ?? null,
                'material_cost' => $data['material_cost'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'status' => Order::STATUS_NEW,
                'customization_notes' => $data['customization_notes'] ?? null,
                'design_image' => $imageUrls[0] ?? null,
                'design_images' => $imageUrls === [] ? null : $imageUrls,
            ]);

            $this->orders->incrementCustomerOrders($customer);

            if ((float) ($data['advance_paid'] ?? 0) > 0) {
                $this->payments->create([
                    'workshop_id' => $workshopId,
                    'order_id' => $order->id,
                    'type' => Payment::TYPE_ORDER_ADVANCE,
                    'amount' => $data['advance_paid'],
                    'mode' => $data['advance_mode'] ?? 'cash',
                    'note' => 'Advance on order '.$order->order_no,
                    'paid_at' => now()->toDateString(),
                ]);
            }

            if ($sendWhatsapp && $customer->phone) {
                SendWhatsAppMessageJob::dispatch(
                    $customer->phone,
                    "Namaste {$customer->name}! Your order {$order->order_no} for '{$order->item_name}' has been received at {$order->workshop->name}. Total: Rs {$order->total_amount}. Advance paid: Rs {$order->advance_paid}. Balance due: Rs {$order->balanceDue()}."
                );
            }

            return $order;
        });
    }

    /**
     * Store design image references. Accepts either base64 data-URIs (uploaded
     * to Cloudinary server-side) or already-hosted URLs (e.g. uploaded directly
     * from the mobile app) which are stored as-is.
     *
     * @param  array<string, mixed>  $data
     * @return string[]
     */
    protected function uploadDesignImages(array $data): array
    {
        $cloudinary = app(CloudinaryService::class);
        $urls = [];

        if (! empty($data['design_images']) && is_array($data['design_images'])) {
            $urls = $this->resolveDesignImageUrls($data['design_images'], $cloudinary);
        }

        if ($urls === [] && ! empty($data['design_image'])) {
            $url = $this->resolveDesignImageUrl($data['design_image'], $cloudinary);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * @param  string[]  $images
     * @return string[]
     */
    protected function resolveDesignImageUrls(array $images, CloudinaryService $cloudinary): array
    {
        $urls = [];

        foreach ($images as $image) {
            $url = $this->resolveDesignImageUrl($image, $cloudinary);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    protected function resolveDesignImageUrl(string $image, CloudinaryService $cloudinary): ?string
    {
        if (str_starts_with($image, 'data:')) {
            return $cloudinary->uploadDataUri($image);
        }

        if (filter_var($image, FILTER_VALIDATE_URL) !== false) {
            return $image;
        }

        return null;
    }

    public function updateStatus(int $id, string $status, ?float $laborCost = null): ?Order
    {
        $order = $this->orders->findForWorkshop($id);

        if ($order === null) {
            return null;
        }

        $updated = $this->orders->updateStatus($order, $status, $laborCost);

        if ($status === Order::STATUS_READY && $updated->customer?->phone) {
            SendWhatsAppMessageJob::dispatch(
                $updated->customer->phone,
                "Namaste {$updated->customer->name}! Your order {$updated->order_no} for '{$updated->item_name}' is ready for delivery at {$updated->workshop->name}. Balance due: Rs {$updated->balanceDue()}. Please collect it soon."
            );
        }

        return $updated;
    }

    public function assignKarigar(int $id, ?int $karigarId): ?Order
    {
        $order = $this->orders->findForWorkshop($id);

        if ($order === null) {
            return null;
        }

        return $this->orders->update($order, ['karigar_id' => $karigarId]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCosting(int $id, array $data): ?Order
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->orders->findForWorkshop($id);

            if ($order === null) {
                return null;
            }

            $order->update([
                'material_cost' => $data['material_cost'] ?? null,
            ]);

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addStage(int $id, array $data): ?Order
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->orders->findForWorkshop($id);

            if ($order === null) {
                return null;
            }

            $stage = $order->stages()->create([
                'workshop_id' => $order->workshop_id,
                'karigar_id' => $data['karigar_id'] ?? null,
                'name' => $data['name'],
                'labor_cost' => $data['labor_cost'] ?? 0,
                'status' => $data['status'] ?? OrderStage::STATUS_PENDING,
            ]);

            if ($stage->status === OrderStage::STATUS_COMPLETED) {
                $stage->completed_at = now();
                $stage->save();
                $this->recordStageAdvanceSettlement($stage);
            }

            if ($order->karigar_id === null && ! empty($data['karigar_id'])) {
                $order->update(['karigar_id' => $data['karigar_id']]);
            }

            $this->syncLaborCost($order);
            $this->syncOrderStatus($order);

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateStage(int $id, int $stageId, array $data): ?Order
    {
        return DB::transaction(function () use ($id, $stageId, $data) {
            $order = $this->orders->findForWorkshop($id);

            if ($order === null) {
                return null;
            }

            $stage = $order->stages()->find($stageId);

            if ($stage === null) {
                return null;
            }

            $update = [];
            foreach (['name', 'karigar_id', 'labor_cost'] as $field) {
                if (array_key_exists($field, $data)) {
                    $update[$field] = $data[$field];
                }
            }

            $wasCompleted = $stage->status === OrderStage::STATUS_COMPLETED;

            if (array_key_exists('status', $data)) {
                $update['status'] = $data['status'];
                $update['completed_at'] = $data['status'] === OrderStage::STATUS_COMPLETED
                    ? now()
                    : null;
            }

            $stage->update($update);

            if (! $wasCompleted && $stage->status === OrderStage::STATUS_COMPLETED) {
                $this->recordStageAdvanceSettlement($stage);
            }

            $this->syncLaborCost($order);
            $this->syncOrderStatus($order);

            return $order;
        });
    }

    public function deleteStage(int $id, int $stageId): ?Order
    {
        return DB::transaction(function () use ($id, $stageId) {
            $order = $this->orders->findForWorkshop($id);

            if ($order === null) {
                return null;
            }

            $stage = $order->stages()->find($stageId);

            if ($stage === null) {
                return null;
            }

            $stage->delete();
            $this->syncLaborCost($order);
            $this->syncOrderStatus($order);

            return $order;
        });
    }

/**
     * Create a karigar ledger entry whenever a stage is completed. It records
     * the labour realised against the karigar's outstanding advance: the amount
     * settled (stage labour cost) and the advance balance left afterwards.
     *
     * The entry is only created while the karigar still has an advance balance
     * to draw from. Once the advance is fully consumed (remaining <= 0) no
     * automatic row is written anymore — from then on the owner settles the
     * karigar manually via "Settle Payout", which creates its own ledger entry.
     *
     * This is a pure bookkeeping row: totals keep coming from `ledgerSummary()`
     * (which sums only advance + settlement types), so existing figures are not
     * disturbed.
     */
    protected function recordStageAdvanceSettlement(OrderStage $stage): void
    {
        if ($stage->karigar_id === null || (float) $stage->labor_cost <= 0) {
            return;
        }

        $advanceTotal = (float) Payment::query()
            ->where('karigar_id', $stage->karigar_id)
            ->where('type', Payment::TYPE_KARIGAR_ADVANCE)
            ->sum('amount');

        // Labour already settled from the advance in previous completed stages
        // (this stage is excluded — it is the one currently being completed).
        $laborBefore = (float) OrderStage::query()
            ->where('karigar_id', $stage->karigar_id)
            ->where('status', OrderStage::STATUS_COMPLETED)
            ->where('id', '!=', $stage->id)
            ->sum('labor_cost');

        $remainingBefore = round($advanceTotal - $laborBefore, 2);

        // Advance fully consumed: stop auto-settling, owner settles manually.
        if ($remainingBefore <= 0) {
            return;
        }

        // Never settle more than the advance actually covers.
        $settleAmount = min((float) $stage->labor_cost, $remainingBefore);

        $this->payments->create([
            'workshop_id' => $stage->workshop_id,
            'order_id' => $stage->order_id,
            'karigar_id' => $stage->karigar_id,
            'stage_id' => $stage->id,
            'type' => Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
            'amount' => $settleAmount,
            'advance_remaining' => round($remainingBefore - $settleAmount, 2),
            'mode' => null,
            'note' => "Stage '{$stage->name}' completed — settled from advance",
            'paid_at' => $stage->completed_at?->toDateString() ?? now()->toDateString(),
        ]);
    }

    /**
     * Keep the legacy `worker_labor_cost` column in sync with the stage-wise
     * work so existing aggregations (karigar ledger, dashboard, reports) stay
     * correct without changing them. The stored value is the sum of COMPLETED
     * stages; profit calculations use {@see Order::laborTotal()} instead.
     */
    protected function syncLaborCost(Order $order): void
    {
        $stages = $order->stages()->get();

        if ($stages->isEmpty()) {
            $order->update(['worker_labor_cost' => null]);

            return;
        }

        $completed = round((float) $stages
            ->where('status', OrderStage::STATUS_COMPLETED)
            ->sum('labor_cost'), 2);

        $order->update(['worker_labor_cost' => $completed]);
    }

    /**
     * Derive the order's status entirely from the production stage flow, so
     * the stage-driven stepper stays the single source of truth:
     *   - work started (any stage in progress/completed)  -> `in_structure`
     *   - every canonical stage assigned and completed     -> `ready`
     * A `new` order is left untouched until a stage actually starts.
     */
    protected function syncOrderStatus(Order $order): void
    {
        $stages = $order->stages()->get();

        if ($stages->isEmpty()) {
            return;
        }

        $assigned = $stages->pluck('name')->all();
        $allCanonicalDone = collect(OrderStage::STAGE_ORDER)
            ->every(fn ($name) => in_array($name, $assigned, true))
            && $stages->every(fn ($stage) => $stage->status === OrderStage::STATUS_COMPLETED);

        $anyStarted = $stages->contains(
            fn ($stage) => in_array($stage->status, [OrderStage::STATUS_IN_PROGRESS, OrderStage::STATUS_COMPLETED], true)
        );

        $status = $allCanonicalDone
            ? Order::STATUS_READY
            : ($anyStarted ? Order::STATUS_IN_STRUCTURE : null);

        if ($status === null || $status === $order->status) {
            return;
        }

        // Never downgrade an order that is already ready/completed.
        if (in_array($order->status, [Order::STATUS_READY, Order::STATUS_COMPLETED], true)) {
            return;
        }

        $order->update(['status' => $status]);
    }

    protected function generateOrderNo(): string
    {
        do {
            $no = 'ORD-'.now()->format('ymd').'-'.Str::upper(Str::random(4));
        } while (Order::where('order_no', $no)->exists());

        return $no;
    }
}
