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

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): array
    {
        $workshop = Workshop::create(['name' => 'Test Workshop']);
        $user = User::create(['workshop_id' => $workshop->id, 'phone' => '9876500001']);
        $token = $user->createToken('test')->plainTextToken;

        return [$workshop, $user, $token];
    }

    public function test_create_and_list_order(): void
    {
        [, , $token] = $this->authUser();

        $karigar = Karigar::create(['workshop_id' => 1, 'name' => 'Asraf']);

        $create = $this->withToken($token)->postJson('/api/v1/orders', [
            'customer_name' => 'Rahul',
            'customer_phone' => '9111111111',
            'item_name' => 'Dining Table',
            'total_amount' => 40000,
            'advance_paid' => 10000,
            'delivery_date' => now()->addDays(5)->toDateString(),
            'karigar_id' => $karigar->id,
            'send_whatsapp' => true,
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('data.order.item_name', 'Dining Table')
            ->assertJsonPath('data.order.balance_due', 30000.00)
            ->assertJsonPath('data.order.status', 'new');

        $orderNo = $create->json('data.order.order_no');
        $this->assertStringStartsWith('ORD-', $orderNo);

        $this->assertDatabaseHas('customers', ['name' => 'Rahul', 'phone' => '9111111111']);
        $this->assertDatabaseHas('payments', ['type' => 'order_advance', 'amount' => 10000.00]);

        $this->withToken($token)
            ->getJson('/api/v1/orders')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.orders');
    }

    public function test_orders_are_scoped_per_workshop(): void
    {
        [$w1, , $t1] = $this->authUser();
        $w2 = Workshop::create(['name' => 'Other Workshop']);
        $u2 = User::create(['workshop_id' => $w2->id, 'phone' => '9876500002']);
        $t2 = $u2->createToken('test')->plainTextToken;

        Order::create([
            'workshop_id' => $w1->id,
            'order_no' => 'ORD-AAA',
            'customer_id' => Customer::create(['workshop_id' => $w1->id, 'name' => 'A'])->id,
            'item_name' => 'Sofa',
            'total_amount' => 100,
        ]);

        $this->withToken($t1)->getJson('/api/v1/orders')->assertJsonCount(1, 'data.orders');

        $this->app['auth']->forgetGuards();

        $this->withToken($t2)->getJson('/api/v1/orders')->assertJsonCount(0, 'data.orders');

        $this->withToken($t2)->getJson('/api/v1/orders/1')->assertStatus(404);
    }

    public function test_same_phone_reuses_customer(): void
    {
        [, , $token] = $this->authUser();

        $first = $this->withToken($token)->postJson('/api/v1/orders', [
            'customer_name' => 'Rahul',
            'customer_phone' => '9111111111',
            'item_name' => 'Dining Table',
            'total_amount' => 40000,
        ]);
        $first->assertStatus(201);

        $this->assertDatabaseCount('customers', 1);

        $second = $this->withToken($token)->postJson('/api/v1/orders', [
            'customer_name' => 'Rahul',
            'customer_phone' => '9111111111',
            'item_name' => 'Bed',
            'total_amount' => 20000,
        ]);
        $second->assertStatus(201);

        $this->assertDatabaseCount('customers', 1);

        $this->withToken($token)
            ->getJson('/api/v1/customers/search?phone=9111111111')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.customers')
            ->assertJsonPath('data.customers.0.name', 'Rahul')
            ->assertJsonPath('data.customers.0.total_orders', 2);
    }

    public function test_order_status_update(): void
    {
        [$w1, , $token] = $this->authUser();
        $order = Order::create([
            'workshop_id' => $w1->id,
            'order_no' => 'ORD-BBB',
            'customer_id' => Customer::create(['workshop_id' => $w1->id, 'name' => 'B'])->id,
            'item_name' => 'Bed',
            'total_amount' => 20000,
        ]);

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'in_structure', 'worker_labor_cost' => 5000])
            ->assertStatus(200)
            ->assertJsonPath('data.order.status', 'in_structure')
            ->assertJsonPath('data.order.worker_labor_cost', 5000.00);

        $this->withToken($token)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'invalid'])
            ->assertStatus(422);
    }

    public function test_order_detail_includes_payments(): void
    {
        [$w1, , $token] = $this->authUser();
        $order = Order::create([
            'workshop_id' => $w1->id,
            'order_no' => 'ORD-CCC',
            'customer_id' => Customer::create(['workshop_id' => $w1->id, 'name' => 'C'])->id,
            'item_name' => 'Chair',
            'total_amount' => 10000,
        ]);
        Payment::create(['workshop_id' => $w1->id, 'order_id' => $order->id, 'type' => 'order_advance', 'amount' => 2000, 'paid_at' => now()->toDateString()]);

        $this->withToken($token)
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.order.payments.0.amount', 2000.00)
            ->assertJsonPath('data.order.amount_received', 2000.00);
    }
}
