<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->string('operational_status', 20)->default('open')->after('default_preparation_time');
            // Reserved for future: busy_preparation_time, schedule_json, vacation_dates
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn('operational_status');
        });
    }
};
