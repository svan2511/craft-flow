<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->integer('total_orders')->default(0);
            $table->timestamps();

            $table->index(['workshop_id', 'phone']);
        });

        Schema::create('karigars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'name']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('order_no')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('karigar_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('advance_paid', 12, 2)->default(0);
            $table->decimal('worker_labor_cost', 12, 2)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('customization_notes')->nullable();
            $table->string('design_image')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'status']);
            $table->index(['workshop_id', 'delivery_date']);
            $table->index(['workshop_id', 'created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('karigar_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type'); // order_advance, order_milestone, order_balance, karigar_advance, karigar_settlement
            $table->decimal('amount', 12, 2);
            $table->string('mode')->nullable(); // cash, online, upi, cheque
            $table->string('note')->nullable();
            $table->date('paid_at')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'type']);
            $table->index(['workshop_id', 'paid_at']);
            $table->index(['workshop_id', 'order_id']);
            $table->index(['workshop_id', 'karigar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('karigars');
        Schema::dropIfExists('customers');
    }
};
