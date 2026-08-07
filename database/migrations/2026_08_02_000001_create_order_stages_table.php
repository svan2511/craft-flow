<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('karigar_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'order_id']);
            $table->index(['workshop_id', 'karigar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stages');
    }
};
