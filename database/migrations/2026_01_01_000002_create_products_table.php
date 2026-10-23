<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('brand', 100);
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('category', 100)->index();
            $table->text('image_url');
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->integer('reviews_count')->default(0);
            $table->text('description')->nullable();
            $table->json('sizes')->nullable();
            $table->json('colors')->nullable();
            $table->json('specs')->nullable();
            $table->string('badge', 50)->nullable();
            $table->boolean('in_stock')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
