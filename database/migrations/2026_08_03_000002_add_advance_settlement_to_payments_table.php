<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('advance_remaining', 12, 2)->nullable()->after('amount');
            $table->index(['workshop_id', 'type', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('advance_remaining');
            $table->dropIndex(['workshop_id', 'type', 'paid_at']);
        });
    }
};