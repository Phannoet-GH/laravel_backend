<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('order_number', 50)->unique();
            $table->string('user_id', 60)->default('guest')->index();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('promo_code', 50)->nullable();
            $table->text('shipping_address');
            $table->string('payment_method', 100);
            $table->string('status', 50)->default('Processing');
            $table->string('tracking_number', 60)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
