<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Karigar;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KarigarFinanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unhandled_exception_returns_generic_message(): void
    {
        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $request = \Illuminate\Http\Request::create('/api/v1/karigars', 'GET');

        $response = $handler->render($request, new \RuntimeException('Internal DB secret detail'));

        $this->assertSame(500, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertSame('Something went wrong. Please try again.', $json['message']);
        $this->assertStringNotContainsString('secret', $json['message']);
    }

    private function makeContext(): array
    {
        $workshop = Workshop::create(['name' => 'Test Workshop']);
        $user = User::create(['workshop_id' => $workshop->id, 'phone' => '9876500011']);
        $token = $user->createToken('test')->plainTextToken;
        $karigar = Karigar::create(['workshop_id' => $workshop->id, 'name' => 'Asraf', 'role' => 'Polish']);

        return [$workshop, $token, $karigar];
    }

    public function test_karigar_store_with_default_rate(): void
    {
        [$workshop, $token] = $this->makeContext();

        $this->withToken($token)
            ->postJson('/api/v1/karigars', [
                'name' => 'Suresh',
                'role' => 'Carpenter',
                'default_rate' => 1500,
                'phone' => '9876500099',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.karigar.default_rate', 1500.00)
            ->assertJsonPath('data.karigar.role', 'Carpenter');

        $this->assertDatabaseHas('karigars', [
            'name' => 'Suresh',
            'default_rate' => 1500.00,
        ]);
    }

    public function test_karigar_default_rate_is_required(): void
    {
        [$workshop, $token] = $this->makeContext();

        $this->withToken($token)
            ->postJson('/api/v1/karigars', [
                'name' => 'Ravi',
                'role' => 'Carpenter',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('default_rate');
    }

    public function test_karigar_advance_and_ledger(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-100',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'X'])->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Sofa',
            'total_amount' => 50000,
            'worker_labor_cost' => 8000,
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$karigar->id}/advances", ['amount' => 3000, 'note' => 'Cash advance'])
            ->assertStatus(201)
            ->assertJsonPath('data.payment.type', 'karigar_advance');

        $this->withToken($token)
            ->getJson("/api/v1/karigars/{$karigar->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.karigar.ledger.total_due', 8000.00)
            ->assertJsonPath('data.karigar.ledger.total_received', 3000.00)
            ->assertJsonPath('data.karigar.ledger.total_pending', 5000.00)
            ->assertJsonPath('data.karigar.ledger.total_advances', 3000.00)
            ->assertJsonPath('data.karigar.ledger.balance', 5000.00);
    }

    public function test_settle_weekly_creates_settlement_payment(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-101',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'Y'])->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Bed',
            'total_amount' => 30000,
            'worker_labor_cost' => 6000,
            'created_at' => now(),
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$karigar->id}/settle-weekly", [])
            ->assertStatus(201)
            ->assertJsonPath('data.payment.type', 'karigar_settlement')
            ->assertJsonPath('data.payment.amount', 6000.00);
    }

    public function test_settle_weekly_rejects_negative_balance(): void
    {
        [, $token, $karigar] = $this->makeContext();

        Payment::create([
            'workshop_id' => $karigar->workshop_id,
            'karigar_id' => $karigar->id,
            'type' => 'karigar_advance',
            'amount' => 5000,
            'paid_at' => now()->startOfWeek()->toDateString(),
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$karigar->id}/settle-weekly", [])
            ->assertStatus(422);
    }

    public function test_receive_payment_updates_order_advance(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-102',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'Z'])->id,
            'item_name' => 'Table',
            'total_amount' => 20000,
            'advance_paid' => 5000,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/payments/receive', [
                'order_id' => $order->id,
                'amount' => 10000,
                'mode' => 'upi',
                'type' => 'order_balance',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.order.advance_paid', 15000.00)
            ->assertJsonPath('data.order.balance_due', 5000.00);
    }

    public function test_milestone_payment_also_reduces_balance_due(): void
    {
        [$workshop, $token] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-103',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'M'])->id,
            'item_name' => 'Almirah',
            'total_amount' => 30000,
            'advance_paid' => 5000,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/payments/receive', [
                'order_id' => $order->id,
                'amount' => 12000,
                'mode' => 'cash',
                'type' => 'order_milestone',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.order.advance_paid', 17000.00)
            ->assertJsonPath('data.order.balance_due', 13000.00);
    }

    public function test_payment_cannot_exceed_remaining_balance(): void
    {
        [$workshop, $token] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-104',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'O'])->id,
            'item_name' => 'Sofa',
            'total_amount' => 20000,
            'advance_paid' => 3000,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/payments/receive', [
                'order_id' => $order->id,
                'amount' => 20000,
                'mode' => 'upi',
                'type' => 'order_balance',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'advance_paid' => 3000.00,
        ]);
    }

    public function test_advance_cannot_be_recorded_twice(): void
    {
        [$workshop, $token] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-105',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'A'])->id,
            'item_name' => 'Bed',
            'total_amount' => 20000,
            'advance_paid' => 3000,
        ]);

        Payment::create([
            'workshop_id' => $workshop->id,
            'order_id' => $order->id,
            'type' => 'order_advance',
            'amount' => 3000,
            'paid_at' => now()->toDateString(),
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/payments/receive', [
                'order_id' => $order->id,
                'amount' => 5000,
                'mode' => 'cash',
                'type' => 'order_advance',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('type');

        // Balance is still accepted on the same order.
        $this->withToken($token)
            ->postJson('/api/v1/payments/receive', [
                'order_id' => $order->id,
                'amount' => 17000,
                'mode' => 'cash',
                'type' => 'order_balance',
            ])
            ->assertStatus(201);
    }

    public function test_reports_summary_shape(): void
    {
        [$workshop, $token] = $this->makeContext();

        $this->withToken($token)
            ->getJson('/api/v1/reports/summary')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'period' => ['today', 'this_week', 'this_month', 'this_year'],
                    'orders_by_status',
                    'outstanding_balance',
                    'karigar_advances',
                    'monthly_revenue',
                ],
            ]);
    }

    public function test_karigar_list_includes_job_counts(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        $customer = Customer::create(['workshop_id' => $workshop->id, 'name' => 'C']);

        Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-111',
            'customer_id' => $customer->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Sofa',
            'total_amount' => 40000,
            'status' => 'in_structure',
        ]);

        Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-112',
            'customer_id' => $customer->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Bed',
            'total_amount' => 30000,
            'status' => 'completed',
        ]);

        Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-113',
            'customer_id' => $customer->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Table',
            'total_amount' => 20000,
            'status' => 'new',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/karigars')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.karigars')
            ->assertJsonPath('data.karigars.0.orders_count', 3)
            ->assertJsonPath('data.karigars.0.active_orders', 1)
            ->assertJsonPath('data.karigars.0.completed_orders', 1)
            ->assertJsonPath('data.karigars.0.pending_orders', 1);
    }

    public function test_karigar_show_includes_per_order_stage_detail(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-110',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'W'])->id,
            'item_name' => 'Sofa',
            'total_amount' => 40000,
            'status' => 'in_structure',
        ]);

        $cuttingId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $karigar->id,
                'labor_cost' => 5000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$cuttingId}", ['status' => 'completed'])
            ->assertStatus(200);

        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Carving',
                'karigar_id' => $karigar->id,
                'labor_cost' => 3000,
            ])
            ->assertStatus(200);

        $res = $this->withToken($token)
            ->getJson("/api/v1/karigars/{$karigar->id}")
            ->assertStatus(200);

        $res->assertJsonPath('data.karigar.orders.0.order_no', 'ORD-110')
            ->assertJsonPath('data.karigar.orders.0.status', 'in_structure')
            ->assertJsonPath('data.karigar.orders.0.current_stage.name', 'Carving')
            ->assertJsonPath('data.karigar.orders.0.current_stage.status', 'pending')
            ->assertJsonPath('data.karigar.orders.0.due', 5000.00)
            ->assertJsonPath('data.karigar.orders.0.received', 0.00)
            ->assertJsonPath('data.karigar.orders.0.pending', 5000.00);
    }

    public function test_settle_weekly_with_order_ties_settlement_to_order(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-120',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'K'])->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Chair',
            'total_amount' => 20000,
            'worker_labor_cost' => 4000,
            'created_at' => now(),
        ]);

        $stageId = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $karigar->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stageId}", ['status' => 'completed']);

        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$karigar->id}/settle-weekly", [
                'order_id' => $order->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.payment.type', 'karigar_settlement')
            ->assertJsonPath('data.payment.order_id', $order->id)
            ->assertJsonPath('data.payment.stage_id', $stageId)
            ->assertJsonPath('data.payment.amount', 4000.00);

        $this->withToken($token)
            ->getJson("/api/v1/karigars/{$karigar->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.karigar.ledger.total_settled', 4000.00)
            ->assertJsonPath('data.karigar.ledger.total_pending', 0.00);
    }

    public function test_advance_with_order_is_tied_to_order(): void
    {
        [$workshop, $token, $karigar] = $this->makeContext();

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-121',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'L'])->id,
            'karigar_id' => $karigar->id,
            'item_name' => 'Table',
            'total_amount' => 30000,
            'worker_labor_cost' => 5000,
            'created_at' => now(),
        ]);

        $this->withToken($token)
            ->postJson("/api/v1/karigars/{$karigar->id}/advances", [
                'amount' => 2000,
                'order_id' => $order->id,
                'note' => 'Raw material advance',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.payment.type', 'karigar_advance')
            ->assertJsonPath('data.payment.order_id', $order->id);

        $this->withToken($token)
            ->getJson("/api/v1/karigars/{$karigar->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.karigar.orders.0.received', 2000.00)
            ->assertJsonPath('data.karigar.orders.0.pending', 3000.00);
    }

    public function test_stage_assigned_karigar_sees_order(): void
    {
        $workshop = Workshop::create(['name' => 'Test Workshop']);
        $user = User::create(['workshop_id' => $workshop->id, 'phone' => '9876500042']);
        $token = $user->createToken('test')->plainTextToken;

        $suresh = Karigar::create(['workshop_id' => $workshop->id, 'name' => 'Suresh', 'role' => 'Cutting']);
        $nilesh = Karigar::create(['workshop_id' => $workshop->id, 'name' => 'Nilesh', 'role' => 'Carving']);

        $order = Order::create([
            'workshop_id' => $workshop->id,
            'order_no' => 'ORD-K2',
            'customer_id' => Customer::create(['workshop_id' => $workshop->id, 'name' => 'N'])->id,
            'item_name' => 'Wardrobe',
            'total_amount' => 60000,
            'material_cost' => 30000,
        ]);

        // Stage 1 -> Suresh (becomes the order lead), completed.
        $stage1Id = $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Structure/Cutting',
                'karigar_id' => $suresh->id,
                'labor_cost' => 4000,
            ])
            ->json('data.order.stages.0.id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stage1Id}", ['status' => 'completed'])
            ->assertStatus(200);

        // Stage 2 -> Nilesh (NOT the lead, only a stage worker), completed.
        $this->withToken($token)
            ->postJson("/api/v1/orders/{$order->id}/stages", [
                'name' => 'Carving',
                'karigar_id' => $nilesh->id,
                'labor_cost' => 2000,
            ])
            ->assertStatus(200);

        $stage2Id = \App\Models\OrderStage::where('order_id', $order->id)->where('name', 'Carving')->value('id');

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/stages/{$stage2Id}", ['status' => 'completed'])
            ->assertStatus(200);

        // Nilesh's detail must list the order with his stage and labour.
        $this->withToken($token)
            ->getJson("/api/v1/karigars/{$nilesh->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.karigar.orders_count', 1)
            ->assertJsonPath('data.karigar.orders.0.id', $order->id)
            ->assertJsonPath('data.karigar.orders.0.current_stage.name', 'Carving')
            ->assertJsonPath('data.karigar.orders.0.current_stage.completed_stages', 1)
            ->assertJsonPath('data.karigar.orders.0.due', 2000.00)
            ->assertJsonPath('data.karigar.active_orders', 0)
            ->assertJsonPath('data.karigar.completed_orders', 1);

        // The list endpoint exposes the same counts.
        $this->withToken($token)
            ->getJson('/api/v1/karigars')
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Nilesh',
                'orders_count' => 1,
                'completed_orders' => 1,
            ]);
    }
}
