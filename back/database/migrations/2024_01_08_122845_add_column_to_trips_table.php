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
        Schema::table('trips', function (Blueprint $table) {
            //add trip amount, payment status, payment method, payment reference
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('cash');
            $table->string('payment_reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            //
        });
    }
};
