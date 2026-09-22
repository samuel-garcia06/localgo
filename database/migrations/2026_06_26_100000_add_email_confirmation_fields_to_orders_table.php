<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->string('email_confirmation_token_hash')->nullable()->after('checkout_token');
            $table->timestamp('email_confirmation_expires_at')->nullable()->after('email_confirmation_token_hash');
            $table->timestamp('email_confirmed_at')->nullable()->after('email_confirmation_expires_at');
            $table->timestamp('verified_at')->nullable()->after('email_confirmed_at');
            $table->timestamp('confirmation_sent_at')->nullable()->after('verified_at');
            $table->unsignedTinyInteger('confirmation_resend_count')->default(0)->after('confirmation_sent_at');

            $table->index(['customer_email', 'created_at']);
            $table->index(['status', 'email_confirmation_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_email', 'created_at']);
            $table->dropIndex(['status', 'email_confirmation_expires_at']);
            $table->dropColumn([
                'customer_email',
                'email_confirmation_token_hash',
                'email_confirmation_expires_at',
                'email_confirmed_at',
                'verified_at',
                'confirmation_sent_at',
                'confirmation_resend_count',
            ]);
        });
    }
};
