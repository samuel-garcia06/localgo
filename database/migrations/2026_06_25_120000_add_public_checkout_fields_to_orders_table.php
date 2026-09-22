<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
            $table->string('checkout_token')->nullable()->after('public_id');
        });

        Order::query()->whereNull('public_id')->eachById(function (Order $order): void {
            $order->forceFill([
                'public_id' => (string) Str::uuid(),
            ])->saveQuietly();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('public_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn(['public_id', 'checkout_token']);
        });
    }
};
