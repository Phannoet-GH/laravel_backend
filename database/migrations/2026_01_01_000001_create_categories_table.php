<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('accent_color', 20)->default('0xFFFF2D6F');
            $table->string('badge', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
