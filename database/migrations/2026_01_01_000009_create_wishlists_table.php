<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->string('id', 60)->primary();
            $table->string('user_id', 60)->nullable()->index();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('icon', 20)->default('✨');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->string('wishlist_id', 60)->index();
            $table->unsignedBigInteger('product_id');
            $table->timestamp('added_at')->useCurrent();

            $table->foreign('wishlist_id')->references('id')->on('wishlists')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
    }
};
