<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('repartidor_id')->nullable()->after('delivery_type')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable()->after('repartidor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('repartidor_id');
            $table->dropColumn('delivered_at');
        });
    }
};
