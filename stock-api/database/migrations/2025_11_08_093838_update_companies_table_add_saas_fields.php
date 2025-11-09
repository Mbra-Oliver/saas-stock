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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('neighborhood');
            $table->string('country')->default('CM')->after('address');
            $table->string('currency')->default('XAF')->after('country');
            $table->string('tax_number')->nullable()->after('currency');
            $table->string('logo')->nullable()->after('tax_number');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('logo');
            $table->enum('subscription_plan', ['free', 'basic', 'premium', 'enterprise'])->default('free')->after('status');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_plan');
            $table->json('settings')->nullable()->after('subscription_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'phone', 'address', 'country', 'currency', 'tax_number',
                'logo', 'status', 'subscription_plan', 'subscription_expires_at', 'settings'
            ]);
        });
    }
};
