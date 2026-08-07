<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('karigar_id')->constrained('order_stages')->nullOnDelete();
            $table->index(['workshop_id', 'stage_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropIndex(['workshop_id', 'stage_id']);
            $table->dropColumn('stage_id');
        });
    }
};
