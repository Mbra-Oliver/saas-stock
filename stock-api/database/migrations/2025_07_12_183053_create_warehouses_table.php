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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Code unique pour l'entrepôt
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('capacity', 10, 2)->nullable(); // Capacité en m²
            $table->enum('type', ['main', 'secondary', 'temporary'])->default('main');
            $table->boolean('active')->default(true);
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamps();


            // Index pour les recherches
            $table->index(['company_id', 'active']);
            $table->index(['code', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
