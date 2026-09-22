<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->string('notification_sound', 30)->default('classic')->after('operational_status');
            $table->boolean('allow_delivery')->default(true)->after('notification_sound');
            $table->boolean('allow_pickup')->default(true)->after('allow_delivery');
            $table->decimal('minimum_order', 8, 2)->default(0)->after('allow_pickup');
            $table->decimal('delivery_fee', 8, 2)->default(2.99)->after('minimum_order');
            $table->text('customer_notice')->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'notification_sound',
                'allow_delivery',
                'allow_pickup',
                'minimum_order',
                'delivery_fee',
                'customer_notice',
            ]);
        });
    }
};
