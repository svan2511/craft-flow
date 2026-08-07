<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Karigar;
use App\Models\Order;
use App\Models\OrderStage;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStageApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeContext(): array
    {
        $workshop = Workshop::create(['name' => 'Test Workshop']);
        $user = User::create(['workshop_id' => $workshop->id, 'phone' => '9876500021']);
        $token = $user->createToken('test')->plainTextToken;
        $asraf = Karigar::create(['workshop_id' => $workshop->id, 'name' => 'Asraf', 'role' => 'Cutting']);
        $ramesh = Karigar::create(['workshop_id' => $workshop->id, 'name' => 'Ramesh', 'role' => 'Polish']);

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-STG',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'S'])->id,
            'item_name' => 'Wooden Bed',
            'total_amount' => 40000,
            'material_cost' => 18000,
        ]);

        return [$workshop, $token, $order, $asraf, $ramesh];
    }

    public function test_add_stage_with_karigar_and_cost(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.order.stages.0.name', 'Structure/Cutting')
            ->assertJsonPath('data.order.stages.0.status', 'pending')
            ->assertJsonPath('data.order.stages.0.karigar.name', 'Asraf')
            ->assertJsonPath('data.order.stages.0.labor_cost', 4000.00)
            ->assertJsonPath('data.order.labor_cost', 4000.00);

        $this->assertDatabaseHas('order_stages', [
            'order_id' => $order->id,
            'name' => 'Structure/Cutting',
            'labor_cost' => 4000.00,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_skip_ahead_in_stage_order(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Polishing',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->assertStatus(200);
    }

    public function test_cannot_assign_next_stage_until_previous_completed(): void
    {
        [, $token, $order, $asraf, $ramesh] = $this->makeContext();

        $cuttingId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Carving',
                'karigar_id' => $ramesh->id,
                'labor_cost' => 3000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$cuttingId}", ['status' => 'completed']);

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Carving',
                'karigar_id' => $ramesh->id,
                'labor_cost' => 3000,
            ])
            ->assertStatus(200);
    }

    public function test_cannot_start_stage_until_previous_completed(): void
    {
        [, $token, $order, $asraf, $ramesh] = $this->makeContext();

        $cuttingId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        // Simulate a Carving stage already assigned before this rule was in place.
        $carvingId = OrderStage::create([
            'order_id' => $order->id,
            'workshop_id' => $order->workshop_id,
            'karigar_id' => $ramesh->id,
            'name' => 'Carving',
            'labor_cost' => 3000,
            'status' => OrderStage::STATUS_PENDING,
        ])->id;

        // Previous stage (Structure/Cutting) is still pending -> cannot start Carving.
        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$carvingId}", ['status' => 'in_progress'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$carvingId}", ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        // Complete the previous stage first, then Carving can progress.
        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$cuttingId}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$carvingId}", ['status' => 'in_progress'])
            ->assertStatus(200);
    }

    public function test_multiple_stages_sum_for_labor_and_profit(): void
    {
        [, $token, $order, $asraf, $ramesh] = $this->makeContext();

        $stages = [
            ['name' => 'Structure/Cutting', 'karigar_id' => $asraf->id, 'labor_cost' => 4000],
            ['name' => 'Carving', 'karigar_id' => $ramesh->id, 'labor_cost' => 3000],
            ['name' => 'Assembly', 'karigar_id' => $asraf->id, 'labor_cost' => 2000],
        ];

        foreach ($stages as $i => $stage) {
            $this->withToken($token)
                ->postJson("/api/v1/orders/{$order->id}/stages", $stage)
                ->assertStatus(200);

            // Complete the current stage so the next one can be assigned.
            $stageId = OrderStage::where('order_id', $order->id)->where('name', $stage['name'])->value('id');

            $this->withToken($token)
                ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", ['status' => 'completed'])
                ->assertStatus(200);
        }

        $res = $this->withToken($token)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertStatus(200);

        // price 40000 - material 18000 - labor 9000 = 13000
        $res->assertJsonPath('data.order.labor_cost', 9000.00)
            ->assertJsonPath('data.order.net_profit', 13000.00);
        $this->assertCount(3, $res->json('data.order.stages'));
    }

    public function test_completing_stage_syncs_legacy_worker_labor_cost(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $stageId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", ['status' => 'completed'])
            ->assertStatus(200)
            ->assertJsonPath('data.order.stages.0.status', 'completed')
            ->assertJsonPath('data.order.worker_labor_cost', 4000.00);

        $this->assertNotNull(OrderStage::find($stageId)->completed_at);
    }

    public function test_update_stage_changes_karigar_and_cost(): void
    {
        [, $token, $order, $asraf, $ramesh] = $this->makeContext();

        $stageId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 2500,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", [
                'karigar_id' => $ramesh->id,
                'labor_cost' => 3000,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.order.stages.0.karigar.name', 'Ramesh')
            ->assertJsonPath('data.order.stages.0.labor_cost', 3000.00);
    }

    public function test_delete_stage_removes_it_and_resyncs(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $stageId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", ['status' => 'completed']);

        $this->withToken($token)
            ->deleteJson("/api/v1/orders/{$order->id}/stages/{$stageId}")
            ->assertStatus(200)
            ->assertJsonCount(0, 'data.order.stages')
            ->assertJsonPath('data.order.worker_labor_cost', null);

        $this->assertDatabaseMissing('order_stages', ['id' => $stageId]);
    }

    public function test_legacy_orders_keep_working_without_stages(): void
    {
        [$workshop, $token, , $asraf] = $this->makeContext();

        $legacy = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-LEG',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'L'])->id,
            'karigar_id' => $asraf->id,
            'item_name' => 'Sofa',
            'total_amount' => 50000,
            'material_cost' => 20000,
            'worker_labor_cost' => 8000,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/orders/{$legacy->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.order.stages', [])
            ->assertJsonPath('data.order.labor_cost', 8000.00)
            ->assertJsonPath('data.order.net_profit', 22000.00);
    }

    public function test_update_material_cost_recomputes_profit(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->assertStatus(200);

        // price 40000 - material 18000 - labor 4000 = 18000
        $this->withToken($token)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertJsonPath('data.order.net_profit', 18000.00);

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/costing", ['material_cost' => 20000])
            ->assertStatus(200)
            ->assertJsonPath('data.order.material_cost', 20000.00)
            ->assertJsonPath('data.order.net_profit', 16000.00);

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/costing", ['material_cost' => null])
            ->assertStatus(200)
            ->assertJsonPath('data.order.material_cost', null)
            ->assertJsonPath('data.order.net_profit', 36000.00);
    }

    public function test_first_stage_karigar_becomes_order_lead(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.order.karigar.name', 'Asraf');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'karigar_id' => $asraf->id,
        ]);
    }

    public function test_order_status_follows_stage_flow(): void
    {
        [, $token, $order, $asraf, $ramesh] = $this->makeContext();

        $stages = [
            ['name' => 'Structure/Cutting', 'karigar_id' => $asraf->id, 'labor_cost' => 4000],
            ['name' => 'Carving', 'karigar_id' => $ramesh->id, 'labor_cost' => 1000],
            ['name' => 'Assembly', 'karigar_id' => $asraf->id, 'labor_cost' => 1000],
            ['name' => 'Sanding/Polishing', 'karigar_id' => $ramesh->id, 'labor_cost' => 1000],
            ['name' => 'Fitting', 'karigar_id' => $asraf->id, 'labor_cost' => 1000],
            ['name' => 'Packaging', 'karigar_id' => $ramesh->id, 'labor_cost' => 1000],
        ];

        // Nothing started yet -> status still new.
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'new']);

        foreach ($stages as $i => $stage) {
            $res = $this->withToken($token)
                ->postJson("/api/v1/orders/{$order->id}/stages", $stage)
                ->assertStatus(200);

            $id = collect($res->json('data.order.stages'))->firstWhere('name', $stage['name'])['id'];

            if ($i === 0) {
                // Starting the first stage kicks the order into in_structure.
                $this->withToken($token)
                    ->patchJson("/api/v1/orders/{$order->id}/stages/{$id}", ['status' => 'in_progress'])
                    ->assertStatus(200)
                    ->assertJsonPath('data.order.status', 'in_structure');
            }

            // Complete the current stage so the next one can be assigned.
            $this->withToken($token)
                ->patchJson("/api/v1/orders/{$order->id}/stages/{$id}", ['status' => 'completed'])
                ->assertStatus(200)
                ->assertJsonPath(
                    'data.order.status',
                    $i === count($stages) - 1 ? 'ready' : 'in_structure'
                );
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'ready',
        ]);
    }

    public function test_stage_completion_without_advance_creates_no_settlement_entry(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        $stageId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('payments', [
            'karigar_id' => $asraf->id,
            'type' => Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
        ]);
    }

    public function test_stage_settlement_never_exceeds_remaining_advance(): void
    {
        [, $token, $order, $asraf] = $this->makeContext();

        // Advance of 5000 given to Asraf.
        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$asraf->id}/advances", [
                'amount' => 5000,
                'note' => 'Material advance',
            ])
            ->assertStatus(201);

        // Complete Structure/Cutting (4000) -> 4000 settled, 1000 advance left.
        $stage1Id = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $asraf->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stage1Id}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'karigar_id' => $asraf->id,
            'type' => Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
            'amount' => 4000.00,
            'advance_remaining' => 1000.00,
        ]);

        // Complete Carving (2000) -> only 1000 advance left, capped at 1000.
        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Carving',
                'karigar_id' => $asraf->id,
                'labor_cost' => 2000,
            ])
            ->assertStatus(200);

        $stage2Id = OrderStage::where('order_id', $order->id)->where('name', 'Carving')->value('id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stage2Id}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'karigar_id' => $asraf->id,
            'type' => Payment::TYPE_KARIGAR_STAGE_SETTLEMENT,
            'amount' => 1000.00,
            'advance_remaining' => 0.00,
        ]);

        // A further stage must no longer auto-settle (advance fully consumed).
        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Assembly',
                'karigar_id' => $asraf->id,
                'labor_cost' => 1500,
            ])
            ->assertStatus(200);

        $stage3Id = OrderStage::where('order_id', $order->id)->where('name', 'Assembly')->value('id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stage3Id}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->assertSame(
            2,
            Payment::where('karigar_id', $asraf->id)
                ->where('type', Payment::TYPE_KARIGAR_STAGE_SETTLEMENT)
                ->count()
        );
    }
}
