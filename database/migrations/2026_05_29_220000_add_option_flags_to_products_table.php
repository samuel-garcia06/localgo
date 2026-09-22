<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_drink_option')->default(false)->after('image_path');
            $table->boolean('has_sauce_option')->default(false)->after('has_drink_option');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_drink_option', 'has_sauce_option']);
        });
    }
};
