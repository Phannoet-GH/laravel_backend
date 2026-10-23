<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 60)->index();
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 255);
            $table->text('product_image')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('selected_size', 50)->nullable();
            $table->string('selected_color', 50)->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
