<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Karigar;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $workshop = Workshop::create([
            'name' => 'Verma Furniture Workshop',
            'owner_name' => 'Ramesh Verma',
            'city' => 'Saharanpur',
            'phone' => '9876543210',
            'whatsapp' => '9876543210',
        ]);

        User::create([
            'workshop_id' => $workshop->id,
            'name' => 'Ramesh Verma',
            'phone' => '9876543210',
        ]);

        $customers = [
            'Rahul Sharma' => '9876543210',
            'Anita Desai' => '9821012345',
            'Meera Gupta' => '9811054321',
            'Suresh Ji' => '9901011111',
            'Priya Verma' => '9760022222',
            'Rajesh Sharma' => '9867543210',
        ];

        $karigars = [
            'Asraf' => 'Polish Master',
            'Vikram' => 'Carpenter',
            'Javed' => 'Finisher',
            'Rahul' => 'Apprentice',
            'Suresh' => 'Carpenter',
            'Imran' => 'Machine Operator',
        ];

        $customerModels = [];
        foreach ($customers as $name => $phone) {
            $customerModels[$name] = Customer::create([
                'workshop_id' => $workshop->id,
                'name' => $name,
                'phone' => $phone,
                'total_orders' => 1,
            ]);
        }

        $karigarModels = [];
        foreach ($karigars as $name => $role) {
            $karigarModels[$name] = Karigar::create([
                'workshop_id' => $workshop->id,
                'name' => $name,
                'role' => $role,
                'phone' => '98'.random_int(10000000, 99999999),
            ]);
        }

        $ordersData = [
            [
                'order_no' => 'ORD-104',
                'customer' => 'Suresh Ji',
                'karigar' => 'Asraf',
                'item_name' => 'Wood Bed 6x6',
                'total' => 40000,
                'paid' => 10000,
                'status' => Order::STATUS_IN_POLISH,
                'delivery' => now()->addDays(2)->toDateString(),
                'labor' => 5000,
            ],
            [
                'order_no' => 'ORD-105',
                'customer' => 'Anita Desai',
                'karigar' => 'Vikram',
                'item_name' => 'Wardrobe - 3 Door',
                'total' => 80000,
                'paid' => 12000,
                'status' => Order::STATUS_IN_STRUCTURE,
                'delivery' => now()->addDay()->toDateString(),
                'labor' => 6000,
            ],
            [
                'order_no' => 'ORD-103',
                'customer' => 'Rahul Sharma',
                'karigar' => 'Vikram',
                'item_name' => 'Dining Table Set',
                'total' => 40000,
                'paid' => 10000,
                'status' => Order::STATUS_IN_STRUCTURE,
                'delivery' => now()->addDays(9)->toDateString(),
                'labor' => 5000,
            ],
            [
                'order_no' => 'ORD-102',
                'customer' => 'Meera Gupta',
                'karigar' => 'Javed',
                'item_name' => 'Teak Sofa Set',
                'total' => 75000,
                'paid' => 30000,
                'status' => Order::STATUS_IN_POLISH,
                'delivery' => now()->addDays(5)->toDateString(),
                'labor' => 5000,
            ],
            [
                'order_no' => 'ORD-098',
                'customer' => 'Meera Gupta',
                'karigar' => 'Javed',
                'item_name' => 'Teak Sofa Set',
                'total' => 75000,
                'paid' => 75000,
                'status' => Order::STATUS_READY,
                'delivery' => now()->addDays(1)->toDateString(),
                'labor' => 2500,
            ],
            [
                'order_no' => 'ORD-095',
                'customer' => 'Priya Verma',
                'karigar' => 'Rahul',
                'item_name' => 'Study Table',
                'total' => 12000,
                'paid' => 4000,
                'status' => Order::STATUS_NEW,
                'delivery' => now()->addDays(12)->toDateString(),
                'labor' => 2500,
            ],
            [
                'order_no' => 'ORD-092',
                'customer' => 'Rajesh Sharma',
                'karigar' => 'Asraf',
                'item_name' => 'Custom Dining Table',
                'total' => 40000,
                'paid' => 25000,
                'status' => Order::STATUS_READY,
                'delivery' => now()->subDay()->toDateString(),
                'labor' => 5000,
                'notes' => 'Dark walnut finish. Ensure edges are softly rounded (bullnose edge). Customer requested extra thick legs for industrial look.',
            ],
            [
                'order_no' => 'ORD-089',
                'customer' => 'Rahul Sharma',
                'karigar' => 'Asraf',
                'item_name' => 'Chair Polish (Set of 4)',
                'total' => 10000,
                'paid' => 10000,
                'status' => Order::STATUS_COMPLETED,
                'delivery' => now()->subDays(8)->toDateString(),
                'labor' => 2500,
            ],
            [
                'order_no' => 'ORD-087',
                'customer' => 'Anita Desai',
                'karigar' => 'Asraf',
                'item_name' => 'King Size Bed',
                'total' => 55000,
                'paid' => 55000,
                'status' => Order::STATUS_COMPLETED,
                'delivery' => now()->subDays(12)->toDateString(),
                'labor' => 5000,
            ],
        ];

        foreach ($ordersData as $data) {
            $order = Order::create([
                'workshop_id' => $workshop->id,
                'order_no' => $data['order_no'],
                'customer_id' => $customerModels[$data['customer']]->id,
                'karigar_id' => $karigarModels[$data['karigar']]->id,
                'item_name' => $data['item_name'],
                'total_amount' => $data['total'],
                'advance_paid' => $data['paid'],
                'worker_labor_cost' => $data['labor'],
                'delivery_date' => $data['delivery'],
                'status' => $data['status'],
                'customization_notes' => $data['notes'] ?? null,
                'created_at' => now()->subDays(random_int(0, 14)),
            ]);

            Payment::create([
                'workshop_id' => $workshop->id,
                'order_id' => $order->id,
                'type' => Payment::TYPE_ORDER_ADVANCE,
                'amount' => $data['paid'],
                'mode' => 'cash',
                'note' => 'Advance on order '.$data['order_no'],
                'paid_at' => now()->subDays(random_int(0, 10))->toDateString(),
            ]);

            if ($data['status'] === Order::STATUS_COMPLETED) {
                Payment::create([
                    'workshop_id' => $workshop->id,
                    'order_id' => $order->id,
                    'type' => Payment::TYPE_ORDER_BALANCE,
                    'amount' => 0,
                    'mode' => 'cash',
                    'note' => 'Order settled',
                    'paid_at' => now()->subDays(3)->toDateString(),
                ]);
            }
        }

        Payment::create([
            'workshop_id' => $workshop->id,
            'karigar_id' => $karigarModels['Asraf']->id,
            'type' => Payment::TYPE_KARIGAR_ADVANCE,
            'amount' => 1000,
            'mode' => 'cash',
            'note' => 'Cash Advance',
            'paid_at' => now()->subDays(3)->toDateString(),
        ]);

        Payment::create([
            'workshop_id' => $workshop->id,
            'karigar_id' => $karigarModels['Asraf']->id,
            'type' => Payment::TYPE_KARIGAR_ADVANCE,
            'amount' => 7500,
            'mode' => 'cash',
            'note' => 'Cash Advance',
            'paid_at' => now()->startOfWeek()->toDateString(),
        ]);
    }
}
