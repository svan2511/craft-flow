<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename the `whatsapp` column to `address` on the workshops table.
     * Only runs when an existing database still has the old column.
     */
    public function up(): void
    {
        if (Schema::hasColumn('workshops', 'whatsapp') && ! Schema::hasColumn('workshops', 'address')) {
            Schema::table('workshops', function (Blueprint $table) {
                $table->renameColumn('whatsapp', 'address');
            });
            Schema::table('workshops', function (Blueprint $table) {
                $table->string('address', 255)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        if (Schema::hasColumn('workshops', 'address') && ! Schema::hasColumn('workshops', 'whatsapp')) {
            Schema::table('workshops', function (Blueprint $table) {
                $table->renameColumn('address', 'whatsapp');
            });
            Schema::table('workshops', function (Blueprint $table) {
                $table->string('whatsapp', 20)->nullable()->change();
            });
        }
    }
};
